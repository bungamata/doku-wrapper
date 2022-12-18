<?php

namespace BungaMata\DokuWrapperTest;

use BungaMata\DokuWrapper\DokuCheckoutV1PaymentUrl;
use BungaMata\DokuWrapper\DokuConfigInterface;
use PHPUnit\Framework\TestCase;

final class DokuCheckoutV1PaymentUrlTest extends TestCase
{
    private function stubConfig(): DokuConfigInterface
    {
        $stubConfig = $this->createStub(DokuConfigInterface::class);
        $stubConfig->method('getAppName')->willReturn('BungaMata-DokuWrapper-unit-test');
        $stubConfig->method('getEndpoint')->willReturn('https://api-sandbox.doku.com');
        $stubConfig->method('getClientId')->willReturn($_SERVER['DOKU_CLIENT_ID']);
        $stubConfig->method('getSecretKey')->willReturn($_SERVER['DOKU_SECRET_KEY']);

        return $stubConfig;
    }

    /**
     * Test if we can correctly retrieve payment URL
     */
    public function testGet(): void
    {
        $url = DokuCheckoutV1PaymentUrl::get(
            $this->stubConfig(),
            [
                "order" => [
                    "amount" => 20000,
                    "invoice_number" => "INV-20210231-0001"
                ],
                "payment" => [
                    "payment_due_date" => 60 // in minutes
                ]
            ]
        );

        $this->assertIsString($url);
        $this->assertStringStartsWith('https://sandbox.doku.com/checkout/link', $url);
    }
}
