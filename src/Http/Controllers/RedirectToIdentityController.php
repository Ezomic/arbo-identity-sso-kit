<?php

namespace RobbinThijssen\IdentitySsoKit\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class RedirectToIdentityController extends Controller
{
    /**
     * Send the browser to Identity's SSO entry point.
     *
     * Laravel's own auth middleware already stashed the originally-intended
     * URL in this app's session before redirecting here, so once the SSO
     * callback re-establishes the session, redirect()->intended() picks it
     * back up — this controller only needs to get the user to Identity.
     *
     * Points at /sso/authorize, not /login directly: Identity's /login is
     * guarded by Fortify's `guest` middleware, which would redirect an
     * already-authenticated Identity session to Identity's own dashboard
     * before ever looking at app/redirect_to — breaking the "switch to a
     * second portal while already logged in" case. /sso/authorize isn't
     * guest-guarded, so it can issue a token immediately for that case,
     * and falls through to /login itself when nobody's logged in yet.
     *
     * Uses Inertia::location() rather than a plain redirect: this link is
     * normally clicked via Inertia's <Link>, which visits via fetch(), and
     * fetch enforces CORS on the cross-origin hop to Identity. location()
     * makes Laravel emit a 409 + X-Inertia-Location header instead, which
     * the Inertia client turns into a real window.location navigation —
     * a full page load isn't subject to CORS the way fetch() is.
     */
    public function __invoke(): Response
    {
        $callbackUrl = route('sso.callback');

        $authorizeUrl = config('sso.identity_base_url').'/sso/authorize?'.http_build_query([
            'app' => config('sso.app_slug'),
            'redirect_to' => $callbackUrl,
        ]);

        return Inertia::location($authorizeUrl);
    }
}
