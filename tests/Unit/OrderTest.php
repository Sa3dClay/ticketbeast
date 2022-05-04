<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Order;
use Tests\CreatesApplication;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

class OrderTest extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseMigrations;
    
    public function test_tickets_are_released_when_an_order_is_cancelled()
    {
        $concert = Concert::factory()->published()->create();
        $concert->addTickets(5);

        $order = $concert->orderTickets('bod@example.com', 2);
        $this->assertEquals(3, $concert->ticketsRemaining());

        $order->cancel();
        $this->assertEquals(5, $concert->ticketsRemaining());

        $this->assertNull(Order::find($order->id));
    }
}
