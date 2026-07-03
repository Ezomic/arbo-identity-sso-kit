<?php

namespace RobbinThijssen\IdentitySsoKit\Sso;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

class SsoTokenVerifier
{
    public function __construct(private readonly IdentityPublicKey $publicKey) {}

    /**
     * Verify an Identity-issued handoff token and return its claims.
     *
     * Checks signature, issuer, and that the token was actually issued for
     * this app (`aud`) — a token minted for another app must not work here.
     */
    public function verify(string $token): VerifiedSsoToken
    {
        $claims = JWT::decode($token, new Key($this->publicKey->get(), 'RS256'));

        if (($claims->iss ?? null) !== 'identity') {
            throw new RuntimeException('SSO token has an unexpected issuer.');
        }

        if (($claims->aud ?? null) !== config('sso.app_slug')) {
            throw new RuntimeException('SSO token was not issued for this app.');
        }

        return VerifiedSsoToken::fromClaims($claims);
    }
}
