<?php

namespace Tests\Unit;

use App\Models\Concert;
use Tests\CreatesApplication;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

class TicketTest extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseMigrations;

    public function test_ticket_can_be_released()
    {
        $concert = Concert::factory()->published()->create();
        $concert->addTickets(1);

        $order = $concert->orderTickets('bod@example.com', 1);
        
        $ticket = $order->tickets()->first();
        $this->assertEquals($order->id, $ticket->order_id);
        $ticket->release();
        $this->assertNull($ticket->fresh()->order_id);
    }
}
