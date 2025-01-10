<?php 

namespace App\Console\Commands;

use DB;
use Http;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncKaroFilmsToFlix extends Command
{
    protected $signature = 'app:sync-karo-films-to-flix';
    protected $description = 'Синхронизация данных KaroFilms с Flix';

    public function handle()
    {
        // Получаем только те cinema_id, где site_id не NULL и больше 0
        $cinemas = DB::table('cinemas')
            ->whereNotNull('site_id')
            ->where('site_id', '>', 0)
            ->whereIn('id', [4, 5])  // Выбираем кинотеатры с id 4 и 5
            ->get(['id', 'site_id', 'flix_id', 'cinema_name', 'site_directory_id']); // Забираем сразу нужные поля

        foreach ($cinemas as $cinema) {
            $this->info("Обрабатываем кинотеатр ID:{$cinema->site_id} {$cinema->cinema_name}");

            // Выполняем запрос к API для получения расписания для всех фильмов в кинотеатре
            $response = Http::get("https://api.karofilm.ru/cinema-schedule", [
                'cinema_id' => $cinema->site_id,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Преобразование данных
                $formattedData = $this->transformData($cinema, $data);

                // Формируем имя файла на основе имени кинотеатра (например: "KARO_11_Октябрь.json")
                $filePath = base_path("{$cinema->cinema_name}.json");

                // Записываем данные в файл, соответствующий кинотеатру
                file_put_contents($filePath, json_encode($formattedData, JSON_PRETTY_PRINT));

                $this->info("Данные для кинотеатра ID: {$cinema->site_id} {$cinema->cinema_name} успешно обработаны и записаны в файл.");
            } else {
                $this->error("Ошибка запроса для кинотеатра ID: {$cinema->site_id} {$cinema->cinema_name}");
            }
        }

        $this->info("Обработка завершена.");
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
                    $kinoplanReleaseId = $directoryData[$movie['id']]['kinoplan_id'];
                    $pushkinCard = $directoryData[$movie['id']]['is_pushkin'];
            
                    // Проходим по каждому формату фильма (например, 2D)
                    foreach ($movie['formats'] as $format) {
                        // Проходим по каждому сеансу в формате
                        foreach ($format['sessions'] as $session) {
                            // Проставляем данные для этого сеанса, связываем с фильмом
                            $allSessions[] = [
                                'kinoplan_release_id' => $kinoplanReleaseId, // kinoplan_id для фильма
                                'pushkin_card' => $pushkinCard, // is_pushkin для фильма
                                'datetime' => $session['showtime'], // Время сеанса
                                'price' => $session['standard_price'], // Цена сеанса
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
                    'sessions' => $allSessions,
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

            Log::info('Directory response:', $data);

            // Проверяем, что ключ 'movie' существует и является массивом
            if (isset($data['data']['movie']) && is_array($data['data']['movie'])) {
                foreach ($data['data']['movie'] as $movie) {
                    $directoryData[$movie['id']] = [
                        'kinoplan_id' => $movie['kinoplan_id'] ?? null,
                        'is_pushkin' => $movie['is_pushkin'] ?? null,
                    ];
                }
            }

            Log::info("Directory data fetched for site_directory_id: {$siteDirectoryId}");
        } else {
            Log::error("Directory request failed with status: {$response->status()}");
        }

        return $directoryData; // Возвращаем данные для всех фильмов
    }
}
