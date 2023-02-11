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
            ->where('expired_at', '>', Carbon::now()->format('Y-m-d H:i:s'))
            ->exists();
    }

    /**
     * If model is not banned.
     */
    public function isNotBanned(): bool
    {
        return ! $this->isBanned();
    }

    public function ban($attributes = []): Ban
    {
        return $this->bans()->create($attributes);
    }

    public function unban(): void
    {
        $this->bans()->each(fn ($ban) => $ban->delete());
    }

    public function scopeBanned(Builder $query): void
    {
        $query->whereHas('bans', function ($query) {
            $query->where('expired_at', '>', Carbon::now()->format('Y-m-d H:i:s'));
        });
    }

    public function scopeNotBanned(Builder $query): void
    {
        $query->whereDoesntHave('bans');
    }
}
