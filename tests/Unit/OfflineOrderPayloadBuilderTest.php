<?php

namespace Tests\Unit;

use App\Services\Mindbox\OfflineOrderPayloadBuilder;
use PHPUnit\Framework\TestCase;

class OfflineOrderPayloadBuilderTest extends TestCase
{
    public function test_it_builds_minimal_payload_without_nulls_and_without_card_number(): void
    {
        $builder = new OfflineOrderPayloadBuilder();

        $order = [
            'transaction_id' => 123,
            'header' => [
                'transaction_membershipid' => 'm123',
                'transaction_bookingId' => 'b456',
                'transaction_time' => '2026-02-02 10:00:00',
                'wcl_cinema_id' => 'cinema_ext_1',
            ],
            'lines' => [
                [
                    'transactionItem_id' => 10,
                    'transactionItem_lineItemCount' => 2,
                    'transactionItem_sessionTime' => '2026-02-02 20:00:00',
                    'item_name' => 'TicketType',
                    'movie_ho' => 'movie_ho_1',
                    // лишние поля в source допустимы, но builder их не должен протащить
                    'movie_name' => 'Movie',
                ],
            ],
        ];

        $payload = $builder->build($order);

        $this->assertSame('m123', $payload['customer']['ids']['membershipID']);
        $this->assertSame('vista_transaction_id_123', $payload['order']['ids']['websiteID']);
        $this->assertSame('b456', $payload['order']['customFields']['bookingId']);
        $this->assertSame('cinema_ext_1', $payload['order']['area']['ids']['externalId']);
        $this->assertArrayHasKey('sessionTime', $payload['order']['customFields']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/', $payload['order']['customFields']['sessionTime']);
        $this->assertArrayNotHasKey('typeTicket', $payload['order']['customFields']);
        // executionDateTimeUtc в формате ISO 8601 UTC
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/', $payload['executionDateTimeUtc']);

        // cardNumber не должен появляться вообще
        $this->assertArrayNotHasKey('discountCard', $payload['customer']);

        // lines: lineNumber, status.ids.externalId = success, product.ids.website, quantity, basePricePerItem
        $this->assertNotEmpty($payload['order']['lines']);
        $this->assertSame(1, $payload['order']['lines'][0]['lineNumber']);
        $this->assertSame('success', $payload['order']['lines'][0]['status']['ids']['externalId']);
        $this->assertSame('movie_ho_1', $payload['order']['lines'][0]['product']['ids']['website']);
        $this->assertSame(1, $payload['order']['lines'][0]['quantity']);
        $this->assertSame('TicketType', $payload['order']['lines'][0]['customFields']['typeTicket']);
    }
}

