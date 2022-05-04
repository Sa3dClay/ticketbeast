<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Models\Concert;
use App\Billing\PaymentGateway;
use App\Exceptions\NotEnoughTicketsException;
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
        try {
            $concert = Concert::published()->findOrFail($concertId);
            
            $order = $concert->orderTickets($request->email, $request->ticket_quantity);

            $amount_to_charge = $request->ticket_quantity * $concert->ticket_price;
            $this->paymentGateway->charge($amount_to_charge, $request->payment_token);
            
            return response()->json(['order' => $order], 201);
        } catch (PaymentFailedException $e) {
            $order->cancel();
            
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }
    }
}
