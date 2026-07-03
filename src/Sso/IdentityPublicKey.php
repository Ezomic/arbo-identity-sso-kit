<?php

namespace RobbinThijssen\IdentitySsoKit\Sso;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IdentityPublicKey
{
    /**
     * Fetch (and cache) the RS256 public key Identity signs SSO tokens with.
     */
    public function get(): string
    {
        return Cache::remember(
            'sso.identity_public_key',
            config('sso.public_key_cache_ttl_seconds'),
            fn () => Http::throw()
                ->get(config('sso.identity_base_url').'/.well-known/identity-public-key')
                ->body(),
        );
    }
}
