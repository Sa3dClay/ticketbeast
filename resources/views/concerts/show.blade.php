<h1>{{ $concert->title }}</h1>
<h2>{{ $concert->subtitle }}</h2>
<small>{{ $concert->date->format('F j, Y, g:ia') }}</small>
<i>{{ $concert->ticket_price }}</i>
<p>{{ $concert->venue }}</p>
<p>{{ $concert->venue_address }}</p>
<p>{{ $concert->city }}</p>
<p>{{ $concert->state }}</p>
<p>{{ $concert->zip }}</p>
<p>{{ $concert->additional }}</p>