<h1>{{ $concert->title }}</h1>
<h2>{{ $concert->subtitle }}</h2>
<small>{{ $concert->formatted_date }} Doors at {{ $concert->formatted_start_time }}</small>
<i>{{ $concert->ticket_price_in_dollars }}</i>
<p>{{ $concert->venue }}</p>
<p>{{ $concert->venue_address }}</p>
<p>{{ $concert->city . ", " . $concert->state . " " . $concert->zip }}</p>
<p>{{ $concert->additional }}</p>