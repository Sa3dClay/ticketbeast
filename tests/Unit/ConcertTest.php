<?php

namespace Tests\Unit;

use Carbon\Carbon;
use App\Models\Concert;
use Tests\CreatesApplication;
use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

class ConcertTest extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseMigrations;

    public function test_can_get_formatted_date()
    {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2016-12-02 8:00am')
        ]);

        $this->assertEquals('December 2, 2016', $concert->formatted_date);
    }

    public function test_can_get_formatted_start_time()
    {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2016-12-02 17:00:00')
        ]);

        $this->assertEquals('5:00pm', $concert->formatted_start_time);
    }

    public function test_can_get_ticket_price_in_dollars()
    {
        $concert = Concert::factory()->make([
            'ticket_price' => 8520
        ]);

        $this->assertEquals('85.20', $concert->ticket_price_in_dollars);
    }

    public function test_concerts_with_published_at_are_published()
    {
        $publishedConcert = Concert::factory()->published()->create();
        $unpublishedConcert = Concert::factory()->unpublished()->create();
        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcert));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    public function test_can_order_concert_tickets()
    {
        $concert = Concert::factory()->published()->create();
        $concert->addTickets(5);
        $order = $concert->orderTickets('bod@example.com', 3);

        $this->assertEquals('bod@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
    }

    public function test_can_add_tickets()
    {
        $concert = Concert::factory()->published()->create();
        $concert->addTickets(50);

        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    public function test_tickets_remaining_does_not_include_ordered_tickets()
    {
        $concert = Concert::factory()->published()->create();
        $concert->addTickets(50);
        $concert->orderTickets('bod@example.com', 30);

        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    public function test_purchasing_more_tickets_than_remaining_throw_an_exception()
    {
        $concert = Concert::factory()->published()->create();
        $concert->addTickets(10);

        try {
            $concert->orderTickets('bod@example.com', 11);
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('bod@example.com'));
            $this->assertEquals(10, $concert->ticketsRemaining());

            return;
        }

        $this->fail("order done even without enough tickets remaining");
    }

    public function test_cannot_order_tickets_that_have_already_been_purchased()
    {
        $concert = Concert::factory()->published()->create();
        $concert->addTickets(10);
        $concert->orderTickets('bod@example.com', 9);

        try {
            $concert->orderTickets('dob@example.com', 2);
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('dob@example.com'));
            $this->assertEquals(1, $concert->ticketsRemaining());

            return;
        }

        $this->fail("order done even with tickets that already purchased");
    }
}
