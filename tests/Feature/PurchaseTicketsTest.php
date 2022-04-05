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

  /**
   * @test
   */
  public function customer_can_purchase_concert_tickets()
  {
    // Arrange
    // - Create Concert
    $concert = Concert::factory()->published()->create(['ticket_price' => 3250]);

    // Act
    // - Purchase Concert Tickets
    $this->json('POST', "concerts/{$concert->id}/orders", [
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

    $this->json('POST', "concerts/{$concert->id}/orders", [
      'ticket_quantity' => 3,
      'payment_token'   => $this->paymentGateWay->getValidTestToken()
    ]);

    $this->assertResponseStatus(422);
    $this->response->assertJsonValidationErrors('email');
  }
}
