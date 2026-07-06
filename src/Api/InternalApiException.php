<?php

namespace RobbinThijssen\IdentitySsoKit\Api;

use RuntimeException;

class InternalApiException extends RuntimeException
{
    private function __construct(
        string $message,
        public readonly int $status,
        public readonly string $responseBody,
    ) {
        parent::__construct($message);
    }

    public static function fromResponse(string $method, string $url, int $status, string $body): self
    {
        return new self(
            "Internal API call {$method} {$url} failed with status {$status}.",
            $status,
            $body,
        );
    }
}
