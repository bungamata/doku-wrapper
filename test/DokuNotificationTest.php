<?php

namespace BungaMata\DokuWrapperTest;

use BungaMata\DokuWrapper\DokuConfigInterface;
use BungaMata\DokuWrapper\DokuNotification;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

final class DokuNotificationTest extends TestCase
{
    public function testValidateFromRequest(): void
    {
        $headersStub = $this->createStub(HeaderBag::class);
        $request = $this->createStub(Request::class);

        // stub requests
        $request->method('getPathInfo')->willReturn('/doku/receive-notification');
        $request->method('getContent')->willReturn(<<<JSON
{
  "order": {
    "invoice_number": "fb648944-7949-11ed-809c-0242ac140002",
    "amount": 450000
  },
  "customer": {
    "name": "John Doe",
    "email": "john.doe@bungamata.com"
  },
  "transaction": {
    "type": "SALE",
    "status": "SUCCESS",
    "date": "2022-12-11T11:51:57Z",
    "original_request_id": "BungaMata-DokuWrapper-6395c4003a192"
  },
  "service": {
    "id": "CREDIT_CARD"
  },
  "acquirer": {
    "id": "BANK_MANDIRI"
  },
  "channel": {
    "id": "CREDIT_CARD"
  },
  "additional_info": {
    "close_redirect": "http://localhost:7211/booking-pending/fb648944-7949-11ed-809c-0242ac140002"
  },
  "card_payment": {
    "masked_card_number": "557338******1135",
    "approval_code": "088544",
    "response_code": "00",
    "response_message": "PAYMENT APPROVED",
    "issuer": "PT. BANK MANDIRI (PERSERO), Tbk"
  }
}
JSON);

        // stub headers
        $headersStub->method('get')->willReturnMap(
            [
                ['client-id', null, 'client_id_foo'],
                ['request-id', null, 'CREDIT_CARD5237642106538937825186023519344121834131129463489239246358495033'],
                ['request-timestamp', null, '2022-12-11T11:51:59Z'],
                ['signature', null, 'HMACSHA256=qOm1meW578ej9K4ApwY6nd+4BzpXUfSv0kaE7sKt/5k=']
            ]
        );
        $request->headers = $headersStub;

        $this->assertTrue(
            DokuNotification::validateFromRequest($this->stubConfig(), $request)
        );
    }

    private function stubConfig(): DokuConfigInterface
    {
        $stubConfig = $this->createStub(DokuConfigInterface::class);
        $stubConfig->method('getAppName')->willReturn('BungaMata-DokuWrapper-unit-test');
        $stubConfig->method('getEndpoint')->willReturn('https://api-sandbox.doku.com');
        $stubConfig->method('getClientId')->willReturn('client_id_foo');
        $stubConfig->method('getSecretKey')->willReturn('secret_key_foo');

        return $stubConfig;
    }
}
