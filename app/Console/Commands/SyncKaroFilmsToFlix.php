<?php

namespace App\Console\Commands;

use App\Models\ManualSync;
use DB;
use Http;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncKaroFilmsToFlix extends Command
{
    protected $signature = 'app:sync-karo-films-to-flix {type=manual}'; // Добавляем аргумент "type" с значением по умолчанию "manual"
    protected $description = 'Синхронизация данных KaroFilms с Flix';

    public function handle()
    {
        $type = $this->argument('type'); // Получаем тип команды
        $syncResults = []; // Для хранения данных по каждой отправке
        $errors = []; // Для хранения ошибок

        // dd('{' . $type . '}');


        // Получаем только те cinema_id, где site_id не NULL и больше 0
        $cinemas = DB::table('cinemas')
            ->whereNotNull('site_id')
            ->whereNull('deleted_at')
            ->where('site_id', '>', 0)
            ->get(['id', 'site_id', 'flix_id', 'cinema_name', 'site_directory_id']);

        foreach ($cinemas as $cinema) {
            $this->info("Обрабатываем кинотеатр ID: {$cinema->site_id} {$cinema->cinema_name}");

            $response = Http::get("https://api.karofilm.ru/cinema-schedule", [
                'cinema_id' => $cinema->site_id,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $formattedData = $this->transformData($cinema, $data);
                $filePath = base_path("Karo_post_logs/{$cinema->cinema_name}.json");
                $directoryPath = base_path('Karo_post_logs');

                // Проверяем, существует ли директория, и создаём её при необходимости
                if (!is_dir($directoryPath)) {
                    mkdir($directoryPath, 0755, true); // true позволяет создать вложенные директории
                }

                // Записываем данные в файл
                file_put_contents($filePath, json_encode($formattedData, JSON_PRETTY_PRINT));

                $result = $this->sendToExternalApi($formattedData, $cinema->cinema_name);
                $syncResults[] = $result;

                // Добавляем ошибку, если запрос завершился неудачно
                if (!$result['success']) {
                    $errors[] = $result['error'];
                }
            } else {
                $errorMessage = "Ошибка запроса для кинотеатра ID: {$cinema->site_id} {$cinema->cinema_name}";
                $this->error($errorMessage);
                $errors[] = ['cinema_name' => $cinema->cinema_name, 'error' => $errorMessage];
            }
        }

        // Определяем статус команды
        $status = empty($errors) ? ManualSync::ACCESS : ManualSync::FAIL;

        // Создаём запись в базе данных
        ManualSync::create([
            'type' => $type,
            'status' => $status,
            'output' => json_encode($syncResults, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ]);

        $this->info("Синхронизация завершена. Тип команды: {$type}. Статус: {$status}");
    }


    /**
     * Преобразование данных для одного кинотеатра.
     */
    private function transformData(object $cinema, array $data): array
    {
        $allSessions = []; // Общий массив для всех сеансов

        // Получаем данные для фильма из directory
        $directoryData = $this->fetchDirectoryDataForCinema($cinema->site_directory_id);

        // Проверяем, что данные в 'data' и 'items' существуют и являются массивом
        if (isset($data['data']['items']) && is_array($data['data']['items'])) {
            // Для каждого фильма из расписания
            foreach ($data['data']['items'] as $movie) {
                // Проверяем, существует ли запись для текущего фильма в $directoryData
                if (isset($directoryData[$movie['id']])) {
                    // Извлекаем kinoplan_id и is_pushkin для фильма
                    $kinoplanReleaseId = (int) $directoryData[$movie['id']]['kinoplan_id']; // Преобразуем в целое число
                    $pushkinCard = $directoryData[$movie['id']]['is_pushkin'];

                    // Пропускаем сеансы, если kinoplanReleaseId равен 0 или null
                    if ($kinoplanReleaseId === 0 || $kinoplanReleaseId === null) {
                        continue; // Пропускаем текущий фильм и его сеансы
                    }

                    // Проходим по каждому формату фильма (например, 2D)
                    foreach ($movie['formats'] as $format) {
                        // Проходим по каждому сеансу в формате
                        foreach ($format['sessions'] as $session) {
                            // Проставляем данные для этого сеанса, связываем с фильмом
                            $allSessions[] = [
                                'kinoplan_release_id' => $kinoplanReleaseId, // kinoplan_id для фильма (целое число)
                                'pushkin_card' => $pushkinCard, // is_pushkin для фильма
                                'datetime' => Carbon::parse($session['showtime'])->format('Y-m-d\TH:i:s\Z'), // Преобразуем дату в формат ISO-8601 с Z
                                'price' => (int) ($session['standard_price'] / 100), // Преобразуем цену в целое число
                                'format_id' => 1, // Статическое значение
                                'external_link' => "https://karofilm.ru/order/session/{$session['id']}" // Формируем ссылку на покупку билетов
                            ];
                        }
                    }
                } else {
                    // Логируем, если фильм отсутствует в данных directory
                    Log::warning("Movie ID {$movie['id']} not found in directory data for cinema ID {$cinema->id}");
                }
            }
        } else {
            Log::warning("No 'items' found for cinema ID: {$cinema->id}");
        }

        // Формируем данные в соответствии с эталонной структурой
        return [
            'schedule' => [
                [
                    'cinema_id' => $cinema->flix_id,
                    'sessions' => $allSessions, // Только сеансы, без лишних данных
                ],
            ],
        ];
    }



    private function fetchDirectoryDataForCinema(int $siteDirectoryId): array
    {
        // Выполняем запрос для получения всех данных из directory
        $response = Http::get("https://api.karofilm.ru/directory/{$siteDirectoryId}");

        $directoryData = [];
        if ($response->successful()) {
            $data = $response->json();
            // Проверяем, что ключ 'movie' существует и является массивом
            if (isset($data['data']['movie']) && is_array($data['data']['movie'])) {
                foreach ($data['data']['movie'] as $movie) {
                    $directoryData[$movie['id']] = [
                        'kinoplan_id' => $movie['kinoplan_id'] ?? null,
                        'is_pushkin' => $movie['is_pushkin'] ?? null,
                    ];
                }
            }
        } else {
            Log::error("Directory request failed with status: {$response->status()}");
        }

        return $directoryData; // Возвращаем данные для всех фильмов
    }

    private function sendToExternalApi(array $data, string $cinemaName): array
    {
        $telegram_message = '';
        $response = Http::withHeaders([
            'App-key' => config('services.flix.token'),
        ])->post(config('services.flix.url') . '/api/schedule/', $data);

        $responseBody = $response->json();
        $status = $responseBody['status'] ?? 'unknown';
        $message = isset($responseBody['message'])
            ? (is_array($responseBody['message']) ? json_encode($responseBody['message']) : $responseBody['message'])
            : 'No message provided';
        $details = isset($responseBody['details']) && is_array($responseBody['details'])
            ? json_encode($responseBody['details'], JSON_PRETTY_PRINT)
            : 'No details provided';

        if ($response->successful() && $status === 'success') {
            return [
                'success' => true,
                'message' => "POST-запрос для {$cinemaName} завершился успешно. Статус: {$status}",
            ];
        } else {
            $telegram_message .= "🛑 POST-запрос для {$cinemaName} завершился с ошибкой." . PHP_EOL . "<b>Статус:</b> {$status}. Message: {$message}." . PHP_EOL . "<b>Details: </b> {$details}" . PHP_EOL . "<a href=\"http://ecom.karofilm.ru/\">ECOM</a>" . PHP_EOL;
            $this->sendToTelegram($telegram_message);
            Log::error("POST-запрос для {$cinemaName} завершился с ошибкой. Статус: {$status}. Message: {$message}. Details: {$details}");
            return [
                'success' => false,
                'cinema' => $cinemaName,
                'error' => $message
            ];
        }
    }

    public function sendToTelegram(string $message)
    {
        $telegramToken = config('services.telegram.token');
        $chats = config('services.telegram.chats');
        $apiUrl = "https://api.telegram.org/bot$telegramToken/sendMessage";

        foreach ($chats as $chat_id) {
            Http::post($apiUrl, [
                'text' => $message,
                'chat_id' => $chat_id,
                'parse_mode' => 'html'
            ]);
        }
    }




}

