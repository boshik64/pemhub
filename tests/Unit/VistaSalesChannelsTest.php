<?php

namespace Tests\Unit;

use App\Services\VistaOfflineOrders\VistaSalesChannels;
use PHPUnit\Framework\TestCase;

class VistaSalesChannelsTest extends TestCase
{
    public function test_it_keeps_only_known_channels_sorted(): void
    {
        $sql = "trans.transaction_salesChannel IN (1, 2, 8)\nAND trans.transaction_id > :last_processed_transaction_id";

        $filtered = VistaSalesChannels::applyChannelFilter($sql, [8, '1', 99, '2); DROP TABLE']);

        $this->assertSame(
            "trans.transaction_salesChannel IN (1, 8)\nAND trans.transaction_id > :last_processed_transaction_id",
            $filtered
        );
    }

    public function test_it_returns_null_when_no_channels_are_enabled(): void
    {
        $this->assertNull(VistaSalesChannels::applyChannelFilter('IN (1, 2, 8)', []));
    }

    public function test_it_names_known_channels(): void
    {
        $this->assertSame('Point of Sale', VistaSalesChannels::name(1));
        $this->assertSame('Kiosk', VistaSalesChannels::name('2'));
        $this->assertSame('Smartix(КСО)', VistaSalesChannels::name(8));
        $this->assertSame('—', VistaSalesChannels::name(null));
        $this->assertSame('15', VistaSalesChannels::name(15));
    }
}
