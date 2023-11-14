<?php

namespace Mchev\Banhammer\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Mchev\Banhammer\Models\Ban;

trait Bannable
{
    /**
     * Get all of the model's bans.
     */
    public function bans(): MorphMany
    {
        return $this->morphMany(Ban::class, 'bannable');
    }

    /**
     * Check if the model is banned.
     */
    public function isBanned(): bool
    {
        return $this->bans->first(function ($ban) {
            return $ban->expired_at === null || $ban->expired_at->isFuture();
        }) !== null;
    }

    /**
     * Check if the model is not banned.
     */
    public function isNotBanned(): bool
    {
        return ! $this->isBanned();
    }

    /**
     * Ban the model with the specified attributes.
     */
    public function ban(array $attributes = []): Ban
    {
        return $this->bans()->create($attributes);
    }

    /**
     * Ban the model until the specified date.
     */
    public function banUntil(string $date): Ban
    {
        return $this->ban([
            'expired_at' => $date,
        ]);
    }

    /**
     * Unban the model by deleting all bans.
     */
    public function unban(): void
    {
        $this->bans()->each(fn ($ban) => $ban->delete());
    }

    /**
     * Scope a query to include only models that are currently banned.
     */
    public function scopeBanned(Builder $query): void
    {
        $query->whereHas('bans', function ($query) {
            $query->notExpired();
        });
    }

    /**
     * Scope a query to include only models that are not currently banned.
     */
    public function scopeNotBanned(Builder $query): void
    {
        $query->whereDoesntHave('bans');
    }

    /**
     * Scope a query to include only models with bans having a specific meta key and value.
     */
    public function scopeWhereBansMeta(Builder $query, string $key, $value): void
    {
        $query->whereHas('bans', function ($query) use ($key, $value) {
            $query->where('metas->'.$key, $value)->notExpired();
        });
    }

    /**
     * Scope a query to include only models with bans created by a specific type.
     */
    public function scopeBannedByType(Builder $query, string $className): void
    {
        $query->whereHas('bans', function ($query) use ($className) {
            $query->where('created_by_type', $className)->notExpired();
        });
    }
}
