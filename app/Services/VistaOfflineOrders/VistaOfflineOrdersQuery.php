<?php

namespace App\Services\VistaOfflineOrders;

use Generator;
use Illuminate\Support\Facades\DB;

/**
 * Выполняет стриминговый SQL-запрос в Vista (SQL Server) и возвращает строки по одной.
 *
 * ВАЖНО:
 * - SQL должен строго соответствовать ТЗ (без TOP/LIMIT).
 * - Ограничение объема делаем на уровне приложения (агрегация по transaction_id).
 */
class VistaOfflineOrdersQuery
{
    /**
     * Реальный SQL-запрос из ТЗ (НЕ менять структуру).
     * Параметр :last_processed_transaction_id обязателен.
     */
    private const SQL = <<<SQL
SELECT
    trans.transaction_id,
    trans.transaction_membershipid,
    trans.transaction_bookingId,
    trans.transaction_time,
    trans.transaction_salesChannel,

    complex.complex_name,
    wcl.wcl_cinema_id,

    item.item_name,
    item.item_nameAltLang,
    item.item_code,

    movie.movie_name,
    movie.movie_code,
    movie.movie_ho,

    transi.transactionItem_lineItemCount,
    transi.transactionItem_spend,
    transi.transactionItem_tax,
    (ISNULL(transi.transactionItem_spend, 0) + ISNULL(transi.transactionItem_tax, 0)) AS transactionItem_spend_with_tax,
    transi.transactionItem_sessionTime

FROM [VISTALOYALTY].[dbo].[cognetic_data_transaction] AS trans

LEFT JOIN [VISTALOYALTY].[dbo].[cognetic_data_transactionItem] AS transi
    ON trans.transaction_id = transi.transactionItem_transactionid

LEFT JOIN [VISTALOYALTY].[dbo].[cognetic_data_item] AS item
    ON transi.transactionItem_itemid = item.item_id

-- FULL OUTER JOIN: чтобы попадали и билеты (есть movie), и продукты (нет movie)
FULL OUTER JOIN [VISTALOYALTY].[dbo].[cognetic_rules_movie] AS movie
    ON transi.transactionItem_movieid = movie.movie_id

LEFT JOIN [dbo].[cognetic_campaigns_complex] AS complex
    ON trans.transaction_complexid = complex.complex_id

LEFT JOIN [dbo].[WebCinemaList] AS wcl
    ON complex.complex_id = wcl.wcl_complex_id

WHERE
    trans.transaction_salesChannel IN (1, 2, 8)
    AND trans.transaction_id > :last_processed_transaction_id

ORDER BY trans.transaction_id ASC
SQL;

    /**
     * @param int $lastProcessedTransactionId
     * @return Generator<int, array<string, mixed>>
     */
    public function stream(int $lastProcessedTransactionId): Generator
    {
        $pdo = DB::connection('vista')->getPdo();
        $stmt = $pdo->prepare(self::SQL);
        $stmt->execute([
            'last_processed_transaction_id' => $lastProcessedTransactionId,
        ]);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            yield $row;
        }
    }
}

