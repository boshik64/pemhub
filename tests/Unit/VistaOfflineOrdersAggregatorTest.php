<?php

namespace Tests\Unit;

use App\Services\VistaOfflineOrders\VistaOfflineOrdersAggregator;
use PHPUnit\Framework\TestCase;

class VistaOfflineOrdersAggregatorTest extends TestCase
{
    public function test_it_groups_rows_by_transaction_id_into_orders(): void
    {
        $aggregator = new VistaOfflineOrdersAggregator();

        $rows = [
            [
                'transaction_id' => 1,
                'transaction_membershipid' => 'm1',
                'transaction_bookingId' => 'b1',
                'transaction_time' => 't1',
                'transaction_salesChannel' => 1,
                'complex_name' => 'c1',
                'wcl_cinema_id' => 'x1',
                'transactionItem_id' => 100,
                'transactionItem_lineItemCount' => 1,
                'transactionItem_sessionTime' => 's1',
                'item_name' => 'type1',
                'movie_ho' => 'ho1',
            ],
            [
                'transaction_id' => 1,
                'transaction_membershipid' => 'm1',
                'transaction_bookingId' => 'b1',
                'transaction_time' => 't1',
                'transaction_salesChannel' => 1,
                'complex_name' => 'c1',
                'wcl_cinema_id' => 'x1',
                'transactionItem_id' => 101,
                'transactionItem_lineItemCount' => 2,
                'transactionItem_sessionTime' => 's2',
                'item_name' => 'type2',
                'movie_ho' => 'ho2',
            ],
            [
                'transaction_id' => 2,
                'transaction_membershipid' => 'm2',
                'transaction_bookingId' => 'b2',
                'transaction_time' => 't2',
                'transaction_salesChannel' => 2,
                'complex_name' => 'c2',
                'wcl_cinema_id' => 'x2',
                'transactionItem_id' => 200,
                'transactionItem_lineItemCount' => 1,
                'transactionItem_sessionTime' => 's3',
                'item_name' => 'type3',
                'movie_ho' => 'ho3',
            ],
        ];

        $orders = iterator_to_array($aggregator->aggregate($rows));

        $this->assertCount(2, $orders);
        $this->assertSame(1, $orders[0]['transaction_id']);
        $this->assertCount(2, $orders[0]['lines']);
        $this->assertSame(2, $orders[1]['transaction_id']);
        $this->assertCount(1, $orders[1]['lines']);
    }
}

