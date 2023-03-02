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
     * If model is not banned.
     */
    public function isBanned(): bool
    {
        return $this->bans()
            ->notExpired()
            ->exists();
    }

    /**
     * If model is not banned.
     */
    public function isNotBanned(): bool
    {
        return !$this->isBanned();
    }

    public function ban(array $attributes = []): Ban
    {
        return $this->bans()->create($attributes);
    }

    public function banUntil(string $date): Ban
    {
        return $this->ban([
            'expired_at' => $date,
        ]);
    }

    public function unban(): void
    {
        $this->bans()->each(fn ($ban) => $ban->delete());
    }

    public function scopeBanned(Builder $query): void
    {
        $query->whereHas('bans', function ($query) {
            $query->notExpired();
        });
    }

    public function scopeNotBanned(Builder $query): void
    {
        $query->whereDoesntHave('bans');
    }

    public function scopeWhereBansMeta(Builder $query, string $key, $value): void
    {
        $query->whereHas('bans', function ($query) use ($key, $value) {
            $query->where('metas->' . $key, $value)->notExpired();
        });
    }
}
