<?php

namespace RobbinThijssen\IdentitySsoKit\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class LogoutController extends Controller
{
    /**
     * Destroy the local session, then send the browser on to end Identity's
     * own SSO session too. Without that second step, Identity stays logged
     * in and the very next visit to any portal (even this one) silently
     * re-authenticates via /sso/authorize — which looks exactly like
     * "logout doesn't work". Uses Inertia::location() rather than a plain
     * redirect since the logout link is clicked via Inertia's <Link>,
     * which visits via fetch(); fetch enforces CORS on this cross-origin
     * hop to Identity, but location() makes the client do a real
     * window.location navigation instead, which isn't subject to CORS.
     */
    public function __invoke(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::location(rtrim(config('sso.identity_base_url'), '/').'/sso/logout');
    }
}
