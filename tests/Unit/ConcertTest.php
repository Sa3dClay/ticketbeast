<?php

namespace Tests\Unit;

use Carbon\Carbon;
use App\Models\Concert;
use Tests\CreatesApplication;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

class ConcertTest extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseMigrations;

    /**
     * @test
     */
    public function can_get_formatted_date()
    {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2016-12-02 8:00am')
        ]);

        $this->assertEquals('December 2, 2016', $concert->formatted_date);
    }

    /**
     * @test
     */
    public function can_get_formatted_start_time()
    {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2016-12-02 17:00:00')
        ]);

        $this->assertEquals('5:00pm', $concert->formatted_start_time);
    }

    /**
     * @test
     */
    public function can_get_ticket_price_in_dollars()
    {
        $concert = Concert::factory()->make([
            'ticket_price' => 8520
        ]);

        $this->assertEquals('85.20', $concert->ticket_price_in_dollars);
    }

    /**
     * @test
     */
    public function concerts_with_published_at_are_published()
    {
        $publishedConcert = Concert::factory()->published()->create([]);
        $unpublishedConcert = Concert::factory()->unpublished()->create([]);

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcert));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }
}
