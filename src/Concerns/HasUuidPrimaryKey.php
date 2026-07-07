<?php

namespace RobbinThijssen\IdentitySsoKit\Concerns;

use Illuminate\Support\Str;

trait HasUuidPrimaryKey
{
    public function initializeHasUuidPrimaryKey(): void
    {
        $this->incrementing = false;
        $this->keyType = 'string';
    }

    protected static function bootHasUuidPrimaryKey(): void
    {
        static::creating(function ($model) {
            $model->{$model->getKeyName()} ??= (string) Str::uuid7();
        });
    }
}
