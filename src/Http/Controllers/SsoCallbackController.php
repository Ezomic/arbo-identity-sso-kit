<?php

namespace RobbinThijssen\IdentitySsoKit\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use RobbinThijssen\IdentitySsoKit\Sso\SsoTokenVerifier;

class SsoCallbackController extends Controller
{
    public function __construct(private readonly SsoTokenVerifier $verifier) {}

    /**
     * Verify the one-shot token Identity redirected the browser here with,
     * sync the local shadow rows, and establish this app's own session.
     *
     * Relies on the consuming app's own App\Models\User / App\Models\Tenant
     * following the shared shadow-table shape (id, name, email,
     * current_role, tenant_id, identity_synced_at) — see identity-sso-kit's
     * README for the expected migrations.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $verified = $this->verifier->verify($request->query('token', ''));

        if ($verified->tenantId !== null) {
            Tenant::query()->updateOrCreate(
                ['id' => $verified->tenantId],
                ['name' => $verified->tenantName ?? $verified->tenantId],
            );
        }

        $user = User::query()->updateOrCreate(
            ['id' => $verified->userUuid],
            [
                'name' => $verified->name,
                'email' => $verified->email,
                'current_role' => $verified->role,
                'tenant_id' => $verified->tenantId,
                'accessible_apps' => $verified->accessibleApps,
                'identity_synced_at' => now(),
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ],
        );

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
