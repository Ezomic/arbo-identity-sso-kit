<?php

namespace RobbinThijssen\IdentitySsoKit\Api;

use RuntimeException;

class InternalApiException extends RuntimeException
{
    public static function fromResponse(string $method, string $url, int $status, string $body): self
    {
        return new self("Internal API call {$method} {$url} failed with status {$status}: {$body}");
    }
}
