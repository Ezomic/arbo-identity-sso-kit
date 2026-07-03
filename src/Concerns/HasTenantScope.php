<?php

namespace RobbinThijssen\IdentitySsoKit\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Scopes a model to the authenticated user's tenant, and auto-fills
 * tenant_id on create. This is the multi-tenant boundary for every
 * tenant-owned table in a consuming app — every model that touches tenant
 * data must use this trait rather than remembering to scope queries manually.
 */
trait HasTenantScope
{
    protected static function bootHasTenantScope(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check() && Auth::user()->tenant_id !== null) {
                $builder->where($builder->getModel()->getTable().'.tenant_id', Auth::user()->tenant_id);
            }
        });

        static::creating(function (Model $model) {
            if ($model->tenant_id === null && Auth::check()) {
                $model->tenant_id = Auth::user()->tenant_id;
            }
        });
    }
}
