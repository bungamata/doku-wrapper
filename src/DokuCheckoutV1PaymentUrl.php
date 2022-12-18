<?php

namespace BungaMata\DokuWrapper;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

/**
 * Static class to get Doku payment URL
 *
 * @see https://dashboard.doku.com/docs/docs/jokul-checkout/jokul-checkout-integration Jokul docs
 */
final class DokuCheckoutV1PaymentUrl
{
    /**
     * @param DokuConfigInterface $config The Doku application configuration
     * @param array<mixed> $payload The payload to send to Doku
     * @param ?LoggerInterface $logger If provided, the function will create log.
     * @return string The payment URL
     * @see https://dashboard.doku.com/docs/docs/jokul-checkout/jokul-checkout-integration example payload
     */
    public static function get(DokuConfigInterface $config, array $payload, LoggerInterface $logger = null): string
    {
        if (empty($payload)) {
            throw new \InvalidArgumentException('Payload cannot be empty');
        }

        $requestId = uniqid($config->getAppName() . '-');
        $requestTimestamp = date(DokuConfigInterface::FORMAT_DATE);
        $payloadString = json_encode($payload);

        if ($payloadString === false) {
            throw new \InvalidArgumentException('Payload cannot be encoded to JSON');
        }

        $digest = base64_encode(hash('sha256', $payloadString, true));

        if ($logger) {
            $logger->info(
                'doku_request_payload',
                [
                    'request_id' => $requestId,
                    'payload' => $payload,
                ]
            );
        }

        $componentSignature = "Client-Id:" . $config->getClientId() . PHP_EOL .
            "Request-Id:" . $requestId . PHP_EOL .
            "Request-Timestamp:" . $requestTimestamp . PHP_EOL .
            "Request-Target:" . DokuTargetPath::CHECKOUT_V1_PAYMENT . PHP_EOL .
            "Digest:" . $digest;

        if ($logger) {
            $logger->debug(
                'doku_component_signature',
                [
                    'request_id' => $requestId,
                    'component_signature' => $componentSignature,
                ]
            );
        }

        $signature = base64_encode(hash_hmac('sha256', $componentSignature, $config->getSecretKey(), true));

        if ($logger) {
            $logger->debug(
                'doku_signature',
                [
                    'request_id' => $requestId,
                    'signature' => $signature,
                ]
            );
        }

        $httpClient = new Client(
            [
                'base_uri' => $config->getEndpoint(),
                'timeout' => 5.0,
            ]
        );

        try {
            $response = $httpClient->post(
                DokuTargetPath::CHECKOUT_V1_PAYMENT,
                [
                    'headers' => [
                        'User-Agent' => $config->getAppName(),
                        'Content-Type' => 'application/json',
                        'Client-Id' => $config->getClientId(),
                        'Request-Id' => $requestId,
                        'Request-Timestamp' => $requestTimestamp,
                        'Signature' => 'HMACSHA256=' . $signature,
                    ],
                    'body' => $payloadString,
                ]
            );
        } catch (GuzzleException $e) {
            if ($logger) {
                $logger->error(
                    'doku_request_error',
                    [
                        'request_id' => $requestId,
                        'error_message' => $e->getMessage(),
                    ]
                );
            }

            throw new \RuntimeException('Cannot get payment URL from Doku. Request id: ' . $requestId);
        }

        /** @var array<mixed> $dokuJsonBody */
        $dokuJsonBody = json_decode($response->getBody()->getContents(), true);

        if ($logger) {
            $logger->info(
                'doku_checkout_payment_url_response',
                [
                    'request_id' => $requestId,
                    'response' => $dokuJsonBody,
                ]
            );
        }

        /** @var ?string $paymentUrl */
        $paymentUrl = $dokuJsonBody['response']['payment']['url'] ?? null;

        if ($paymentUrl === null) {
            if ($logger) {
                $logger->error(
                    'doku_checkout_payment_url_empty',
                    [
                        'request_id' => $requestId,
                        'response' => $dokuJsonBody,
                    ]
                );
            }

            throw new \RuntimeException('Cannot get payment URL from Doku. Request id: ' . $requestId);
        }

        return $paymentUrl;
    }
}
