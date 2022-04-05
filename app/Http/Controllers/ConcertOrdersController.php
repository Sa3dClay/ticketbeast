<?php

namespace App\Http\Controllers;

use App\Models\Concert;
use App\Billing\PaymentGateway;
use App\Http\Requests\CreateConcertOrderRequest;

class ConcertOrdersController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store(CreateConcertOrderRequest $request, $concertId)
    {
        $concert = Concert::find($concertId);
        $amount_to_charge = $request->ticket_quantity * $concert->ticket_price;
        $this->paymentGateway->charge($amount_to_charge, $request->payment_token);
        $order = $concert->orderTickets($request->email, $request->ticket_quantity);

        return response()->json(['order' => $order], 201);
    }
}
