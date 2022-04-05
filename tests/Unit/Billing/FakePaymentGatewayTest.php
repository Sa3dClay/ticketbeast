<?php

namespace Tests\Unit\Billing;

use Tests\CreatesApplication;
use App\Billing\FakePaymentGateway;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

class FakePaymentGatewayTest extends BaseTestCase
{
  use CreatesApplication;

  /**
   * @test
   */
  public function charges_with_a_valid_payment_token_are_successful()
  {
    $paymentGateway = new FakePaymentGateway;

    $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

    $this->assertEquals(2500, $paymentGateway->totalCharges());
  }
}
