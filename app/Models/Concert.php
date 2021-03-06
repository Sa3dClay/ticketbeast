<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $dates = ['date'];

    // - Relations
    public function orders() {
        return $this->hasMany(Order::class);
    }

    // - Actions
    public function orderTickets($email, $ticketQuantity)
    {
        $order = $this->orders()->create(['email' => $email]);

        foreach(range(1, $ticketQuantity) as $i) {
            $order->tickets()->create([]);
        }

        return $order;
    }

    // - Getters
    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }

    // - Scopes
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
}
