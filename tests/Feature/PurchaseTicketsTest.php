<?php

use App\Models\Concert;
use Tests\CreatesApplication;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

class PurchaseTicketsTest extends BaseTestCase
{
  use DatabaseMigrations;
  use CreatesApplication;

  public $baseUrl = 'http://127.0.0.1:8000';

  protected function setUp(): void
  {
    parent::setUp();

    $this->paymentGateway = new FakePaymentGateway;
    $this->app->instance(PaymentGateway::class, $this->paymentGateway);
  }

  private function orderTickets($concert, $params)
  {
    $this->json('POST', "concerts/{$concert->id}/orders", $params);
  }

  private function assertValidationError($field)
  {
    $this->assertResponseStatus(422);
    $this->response->assertJsonValidationErrors($field);
  }

  public function test_customer_can_purchase_tickets_to_a_published_concert()
  {
    $concert = Concert::factory()->published()->create(['ticket_price' => 3250]);
    $concert->addTickets(5);

    $this->orderTickets($concert, [
      'email'           => 'bod@example.com',
      'ticket_quantity' => 3,
      'payment_token'   => $this->paymentGateway->getValidTestToken()
    ]);

    $this->assertResponseStatus(201);

    $this->assertEquals(9750, $this->paymentGateway->totalCharges());

    $this->assertTrue($concert->hasOrderFor('bod@example.com'));
    $this->assertEquals(3, $concert->ordersFor('bod@example.com')->first()->ticketQuantity());
  }

  public function test_cannot_purchase_tickets_to_an_unpublished_concert()
  {
    $concert = Concert::factory()->unpublished()->create();
    $concert->addTickets(5);

    $this->orderTickets($concert, [
      'email'           => 'bod@example.com',
      'ticket_quantity' => 3,
      'payment_token'   => $this->paymentGateway->getValidTestToken()
    ]);

    $this->assertResponseStatus(404);
    $this->assertFalse($concert->hasOrderFor('bod@example.com'));
    $this->assertEquals(0, $this->paymentGateway->totalCharges());
  }

  public function test_an_order_is_not_created_if_payment_fails()
  {
    $concert = Concert::factory()->published()->create(['ticket_price' => 3250]);
    $concert->addTickets(5);

    $this->orderTickets($concert, [
      'email'           => 'bod@example.com',
      'ticket_quantity' => 3,
      'payment_token'   => 'invalid-payment-token'
    ]);
    
    $this->assertResponseStatus(422);

    $this->assertFalse($concert->hasOrderFor('bod@example.com'));
  }

  public function test_cannot_purchase_more_tickets_than_remain()
  {
    $concert = Concert::factory()->published()->create();
    $concert->addTickets(50);

    $this->orderTickets($concert, [
      'email'           => 'bod@example.com',
      'ticket_quantity' => 51,
      'payment_token'   => $this->paymentGateway->getValidTestToken()
    ]);

    $this->assertResponseStatus(422);

    $this->assertFalse($concert->hasOrderFor('bod@example.com'));
    
    $this->assertEquals(0, $this->paymentGateway->totalCharges());
    $this->assertEquals(50, $concert->ticketsRemaining());
  }

  public function test_email_is_required_to_purchase_tickets()
  {
    $concert = Concert::factory()->published()->create();

    $this->orderTickets($concert, [
      'ticket_quantity' => 3,
      'payment_token'   => $this->paymentGateway->getValidTestToken()
    ]);

    $this->assertValidationError('email');
  }

  public function test_email_must_be_valid_to_purchase_tickets()
  {
    $concert = Concert::factory()->published()->create();

    $this->orderTickets($concert, [
      'email'           => 'bodExample.com',
      'ticket_quantity' => 3,
      'payment_token'   => $this->paymentGateway->getValidTestToken()
    ]);

    $this->assertValidationError('email');
  }

  public function test_payment_token_is_required_to_purchase_tickets()
  {
    $concert = Concert::factory()->published()->create();

    $this->orderTickets($concert, [
      'email'           => 'bod@example.com',
      'ticket_quantity' => 3
    ]);

    $this->assertValidationError('payment_token');
  }

  public function test_ticket_quantity_is_required_to_purchase_tickets()
  {
    $concert = Concert::factory()->published()->create();

    $this->orderTickets($concert, [
      'email'           => 'bod@example.com',
      'payment_token'   => $this->paymentGateway->getValidTestToken()
    ]);

    $this->assertValidationError('ticket_quantity');
  }

  public function test_ticket_quantity_is_at_least_one_to_purchase_tickets()
  {
    $concert = Concert::factory()->published()->create();

    $this->orderTickets($concert, [
      'email'           => 'bod@example.com',
      'ticket_quantity' => -1,
      'payment_token'   => $this->paymentGateway->getValidTestToken()
    ]);

    $this->assertValidationError('ticket_quantity');
  }
}
