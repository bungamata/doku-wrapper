<?php

namespace BungaMata\DokuWrapper;

interface DokuConfigInterface
{
    /**
     * Format date accepted by Doku
     */
    public const FORMAT_DATE = 'Y-m-d\TH:i:sp';

    /**
     * To identify this application name who make the request
     */
    public function getAppName(): string;

    /**
     * Doku URL endpoint based on which ENV are you using.
     * @see DokuEndpoint possible options
     */
    public function getEndpoint(): string;

    /**
     * Doku client ID credential.
     */
    public function getClientId(): string;

    /**
     * Doku secret key credential.
     */
    public function getSecretKey(): string;
}
