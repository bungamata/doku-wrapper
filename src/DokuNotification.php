<?php

namespace BungaMata\DokuWrapper;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Static class that handle Doku notification to our application.
 * @see https://dashboard.doku.com/docs/docs/http-notification/overview/ for docs
 */
final class DokuNotification
{
    /**
     * Validate doku notification signature from HTTP request
     */
    public static function validateFromRequest(
        DokuConfigInterface $config,
        Request $request,
        LoggerInterface $logger = null
    ): bool {
        $clientId = $request->headers->get('client-id');
        $requestId = $request->headers->get('request-id');
        $requestTimestamp = $request->headers->get('request-timestamp');
        $requestTarget = $request->getPathInfo();
        $digest = base64_encode(hash('sha256', $request->getContent(), true));

        $componentSignature = "Client-Id:" . $clientId . PHP_EOL .
            "Request-Id:" . $requestId . PHP_EOL .
            "Request-Timestamp:" . $requestTimestamp . PHP_EOL .
            "Request-Target:" . $requestTarget . PHP_EOL .
            "Digest:" . $digest;

        if ($logger) {
            $logger->debug(
                'doku_notification_component_signature',
                [
                    'request_id' => $requestId,
                    'component_signature' => $componentSignature,
                ]
            );
        }

        $expectedSignature = base64_encode(hash_hmac('sha256', $componentSignature, $config->getSecretKey(), true));
        $actualSignature = str_replace('HMACSHA256=', '', $request->headers->get('signature') ?? '');

        return $expectedSignature === $actualSignature;
    }
}
