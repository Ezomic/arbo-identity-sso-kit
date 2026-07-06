<?php

namespace RobbinThijssen\IdentitySsoKit\Sso;

class VerifiedSsoToken
{
    public function __construct(
        public readonly string $userUuid,
        public readonly string $email,
        public readonly string $name,
        public readonly ?string $tenantId,
        public readonly ?string $tenantName,
        public readonly ?string $role,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $phoneNumber = null,
        public readonly ?string $preferredLocale = null,
        public readonly ?string $timezone = null,
        /**
         * Opaque scope narrower than the tenant — e.g. the specific
         * Employer a `employer_contact` role is restricted to. Identity
         * never interprets this, it just stores and forwards it.
         */
        public readonly ?string $scopeId = null,
        /**
         * Other portals reachable from the person's linked accounts —
         * {slug, name, base_url, as} only, never their role/tenant in
         * those apps. `as` is the display name of the account you'd
         * become there (switching portals switches accounts). Powers a
         * "switch portal" link without the target app calling Identity back.
         *
         * @var array<int, array{slug: string, name: string, base_url: string, as: string}>
         */
        public readonly array $accessibleApps = [],
    ) {}

    public static function fromClaims(object $claims): self
    {
        return new self(
            userUuid: $claims->sub,
            email: $claims->email,
            name: $claims->name,
            tenantId: $claims->tenant_id ?? null,
            tenantName: $claims->tenant_name ?? null,
            role: $claims->role ?? null,
            firstName: $claims->first_name ?? null,
            lastName: $claims->last_name ?? null,
            phoneNumber: $claims->phone_number ?? null,
            preferredLocale: $claims->preferred_locale ?? null,
            timezone: $claims->timezone ?? null,
            scopeId: $claims->scope_id ?? null,
            accessibleApps: array_map(
                fn ($app) => ['slug' => $app->slug, 'name' => $app->name, 'base_url' => $app->base_url, 'as' => $app->as],
                $claims->apps ?? [],
            ),
        );
    }
}
