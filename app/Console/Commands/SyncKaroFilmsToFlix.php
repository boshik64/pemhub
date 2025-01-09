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
            ->get(['id', 'site_id', 'flix_id', 'cinema_name']); // Забираем сразу нужные поля

        $allData = ['schedule' => []];

        foreach ($cinemas as $cinema) {
            $this->info("Обрабатываем кинотеатр ID:{$cinema->site_id} {$cinema->cinema_name}");


            // Выполняем запрос к API
            $response = Http::get("https://api.karofilm.ru/cinema-schedule", [
                'cinema_id' => $cinema->site_id,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Преобразование данных
                $formattedData = $this->transformData($cinema, $data);
                $allData['schedule'][] = $formattedData;

                $this->info("Данные для кинотеатра ID: {$cinema->site_id} {$cinema->cinema_name} успешно обработаны.");
            } else {
                $this->error("Ошибка запроса для кинотеатра ID: {$cinema->site_id} {$cinema->cinema_name}");
            }
        }

        $this->info("Обработка завершена. Всего собрано данных: " . count($allData['schedule']));

        // Записываем данные в файл в корне проекта
        $filePath = base_path('cinema_data.json'); // Путь к файлу в корне проекта
        file_put_contents($filePath, json_encode($allData, JSON_PRETTY_PRINT));

        // Логируем путь, куда записан файл
        Log::info('File saved to: ' . $filePath);

        // Здесь можно отправить $allData в POST-запрос, если нужно
    }

    /**
     * Преобразование данных для одного кинотеатра.
     */
    private function transformData(object $cinema, array $data): array
    {
        $sessions = [];

        // Проверяем, что данные в 'data' и 'items' существуют и являются массивом
        if (isset($data['data']['items']) && is_array($data['data']['items'])) {
            foreach ($data['data']['items'] as $movie) {
                // Проходим по каждому формату фильма (например, 2D)
                foreach ($movie['formats'] as $format) {
                    // Создаём массив для сеансов
                    $movieSessions = [];

                    // Проходим по каждому сеансу в формате
                    foreach ($format['sessions'] as $session) {
                        // Собираем информацию по сеансам
                        $movieSessions[] = [
                            'kinoplan_release_id' => 1997, // Статическое значение
                            'pushkin_card' => true, // Всегда true
                            'datetime' => $session['showtime'], // Берём значение из `showtime`
                            'price' => $session['standard_price'], // Цена из `standard_price`
                            'format_id' => 1, // Статическое значение
                            'external_link' => "https://karofilm.ru/order/session/{$session['id']}" // Формируем ссылку на покупку билетов
                        ];
                    }

                    // Добавляем данные для каждого кинотеатра
                    $sessions[] = [
                        'cinema_id' => $cinema->flix_id, // Получаем flix_id из базы данных
                        'sessions' => $movieSessions // Массив сеансов для текущего фильма
                    ];
                }
            }
        } else {
            Log::warning("No 'items' found for cinema ID: {$cinema->id}");
        }

        return ['schedule' => $sessions];
    }
}
