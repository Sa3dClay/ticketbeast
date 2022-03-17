<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Concert;

class ViewConcertListingTest extends TestCase
{
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

        // Act
        // View the concert listing
        $this->visit('/concert/' . $concert->id);

        // Assert
        // See the concert details
        $this->see('Title');
    }
}
