<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;
use Tests\CreatesApplication;

class ViewConcertListingTest extends BaseTestCase
{
    use DatabaseMigrations;
    use CreatesApplication;

    public $baseUrl = 'http://127.0.0.1:8000';

    /**
     * @test
     */
    public function user_can_view_concert_listing()
    {
        // Arrange
        // Create a concert
        $concert = Concert::create([
            'title'         => 'Title',
            'subtitle'      => 'Subtitle',
            'date'          => Carbon::parse('December 10, 2020, 8:00am'),
            'ticket_price'  => 3520,
            'venue'         => 'The Venue',
            'venue_address' => 'Venue Address',
            'city'          => 'Cairo',
            'state'         => 'On',
            'zip'           => '19177',
            'additional'    => 'For other details call (55)'
        ]);

        // Act & Assert
        // Visit the concert listing
        // See the concert details
        $this->visit('/concerts/' . $concert->id)
            ->see('Title')
            ->see('Subtitle')
            ->see('December 10, 2020, 8:00am')
            ->see('35.20')
            ->see('The Venue')
            ->see('Venue Address')
            ->see('Cairo')
            ->see('On')
            ->see('19177')
            ->see('For other details call (55)');
    }
}
