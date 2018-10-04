<?php

declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Exceptions;

class DatabaseQueryException extends SessionException
{
    public function __construct(string $message = '', string $apiUrl, $payload = [])
    {
        $message = sprintf("call to %s failed. with message '%s'. Payload %s",
            $apiUrl,
            $message,
            count($payload) ? json_encode($payload) : 'none'
        );
        parent::__construct($message);
    }
}
