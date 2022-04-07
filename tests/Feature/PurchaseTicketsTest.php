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

    // - Create PaymentGateway
    $this->paymentGateWay = new FakePaymentGateway;
    $this->app->instance(PaymentGateway::class, $this->paymentGateWay);
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

  /**
   * @test
   */
  public function customer_can_purchase_tickets_to_a_published_concert()
  {
    // Arrange
    // - Create Concert
    $concert = Concert::factory()->published()->create(['ticket_price' => 3250]);

    // Act
    // - Purchase Concert Tickets
    $this->orderTickets($concert, [
      'email'           => 'bod@example.com',
      'ticket_quantity' => 3,
      'payment_token'   => $this->paymentGateWay->getValidTestToken()
    ]);

    // Assert
    // - Make sure the response is success
    $this->assertResponseStatus(201);

    // - Make sure the customer was charged with correct amount
    $this->assertEquals(9750, $this->paymentGateWay->totalCharges());

    // - Make sure the customer make an order with this tickets
    $order = $concert->orders()->where('email', 'bod@example.com')->first();
    $this->assertNotNull($order);
    $this->assertEquals(3, $order->tickets()->count());
  }

  /**
   * @test
   */
  public function cannot_purchase_tickets_to_an_unpublished_concert()
  {
    $concert = Concert::factory()->unpublished()->create();

    $this->orderTickets($concert, [
      'email'           => 'bod@example.com',
      'ticket_quantity' => 3,
      'payment_token'   => $this->paymentGateWay->getValidTestToken()
    ]);

    $this->assertResponseStatus(404);
    // - make sure user no orders created even with valid data
    $this->assertEquals(0, $concert->orders()->count());
    // - make sure user not charged even with a valid payment token
    $this->assertEquals(0, $this->paymentGateWay->totalCharges());
  }

  /**
   * @test
   */
  public function an_order_is_not_created_if_payment_fails()
  {
    $concert = Concert::factory()->published()->create(['ticket_price' => 3250]);

    $this->orderTickets($concert, [
      'email'           => 'bod@example.com',
      'ticket_quantity' => 3,
      'payment_token'   => 'invalid-payment-token'
    ]);
    
    $this->assertResponseStatus(422);

    $order = $concert->orders()->where('email', 'bod@example.com')->first();

    $this->assertNull($order);
  }

  /**
   * @test
   */
  public function can_order_concert_tickets()
  {
    $concert = Concert::factory()->published()->create([]);

    $order = $concert->orderTickets('bod@example.com', 3);

    $this->assertEquals('bod@example.com', $order->email);
    $this->assertEquals(3, $order->tickets()->count());
  }

  /**
   * @test
   */
  public function email_is_required_to_purchase_tickets()
  {
    $concert = Concert::factory()->published()->create([]);

    $this->orderTickets($concert, [
      'ticket_quantity' => 3,
      'payment_token'   => $this->paymentGateWay->getValidTestToken()
    ]);

    $this->assertValidationError('email');
  }

  /**
   * @test
   */
  public function email_must_be_valid_to_purchase_tickets()
  {
    $concert = Concert::factory()->published()->create([]);

    $this->orderTickets($concert, [
      'email'           => 'bodExample.com',
      'ticket_quantity' => 3,
      'payment_token'   => $this->paymentGateWay->getValidTestToken()
    ]);

    $this->assertValidationError('email');
  }

  /**
   * @test
   */
  public function payment_token_is_required_to_purchase_tickets()
  {
    $concert = Concert::factory()->published()->create([]);

    $this->orderTickets($concert, [
      'email'           => 'bod@example.com',
      'ticket_quantity' => 3
    ]);

    $this->assertValidationError('payment_token');
  }

  /**
   * @test
   */
  public function ticket_quantity_is_required_to_purchase_tickets()
  {
    $concert = Concert::factory()->published()->create([]);

    $this->orderTickets($concert, [
      'email'           => 'bod@example.com',
      'payment_token'   => $this->paymentGateWay->getValidTestToken()
    ]);

    $this->assertValidationError('ticket_quantity');
  }

  /**
   * @test
   */
  public function ticket_quantity_is_at_least_one_to_purchase_tickets()
  {
    $concert = Concert::factory()->published()->create([]);

    $this->orderTickets($concert, [
      'email'           => 'bod@example.com',
      'ticket_quantity' => -1,
      'payment_token'   => $this->paymentGateWay->getValidTestToken()
    ]);

    $this->assertValidationError('ticket_quantity');
  }
}
