<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConcertFactory extends Factory
{
  public function definition()
  {
    return [
      'title'         => 'Title',
      'subtitle'      => 'Subtitle',
      'date'          => Carbon::parse('+2 weeks'),
      'ticket_price'  => 3520,
      'venue'         => 'The Venue',
      'venue_address' => 'Venue Address',
      'city'          => 'Cairo',
      'state'         => 'On',
      'zip'           => '19177',
      'additional'    => 'For other details call (55)'
    ];
  }

  public function published()
  {
    return $this->state(function (array $attribute) {
      return [
        'published_at' => Carbon::parse('now')
      ];
    });
  }

  public function unpublished()
  {
    return $this->state(function (array $attribute) {
      return [
        'published_at' => null
      ];
    });
  }
}
