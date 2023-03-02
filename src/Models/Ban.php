<?php

namespace Mchev\Banhammer\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Ban extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by_type',
        'created_by_id',
        'comment',
        'ip',
        'expired_at',
        'metas'
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'metas' => 'array'
    ];

    protected function expiredAt(): Attribute
    {
        return Attribute::make(
            set: fn (null|string|Carbon $value) => (!is_null($value) && !$value instanceof Carbon) ? Carbon::parse($value) : $value,
        );
    }

    /**
     * Get the parent bannable model.
     */
    public function bannable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Entity responsible for ban.
     */
    public function createdBy(): MorphTo
    {
        return $this->morphTo('created_by');
    }

    public function scopePermanent(Builder $query): void
    {
        $query->whereNull('expired_at');
    }

    public function scopeNotPermanent(Builder $query): void
    {
        $query->whereNotNull('expired_at');
    }

    public function scopeExpired(Builder $query): void
    {
        $query->notPermanent()->where('expired_at', '<=', Carbon::now()->format('Y-m-d H:i:s'));
    }

    public function scopeNotExpired(Builder $query): void
    {
        $query->where('expired_at', '>', Carbon::now()->format('Y-m-d H:i:s'))
            ->orWhereNull('expired_at');
    }

    public function scopeWhereMeta(Builder $query, string $name, $value): void
    {
        $query->whereJsonContains('metas->' . $name, $value);
    }

    public function hasMeta(string $propertyName): bool
    {
        return Arr::has($this->metas, $propertyName);
    }

    /**
     * Get the value of meta with the given name.
     *
     * @param string $propertyName
     * @param mixed $default
     *
     * @return mixed
     */
    public function getMeta(string $propertyName, $default = null): mixed
    {
        return Arr::get($this->metas, $propertyName, $default);
    }

    /**
     * Set the value of meta with the given name.
     * @param mixed $value
     *
     * @return $this
     */
    public function setMeta(string $name, $value): self
    {
        $meta = $this->metas;
        Arr::set($meta, $name, $value);
        $this->metas = $meta;

        return $this;
    }

    /**
     * Forget the value of meta with the given name.
     * @param mixed $value
     *
     * @return $this
     */
    public function forgetMeta(string $name): self
    {
        $meta = $this->metas;
        Arr::forget($meta, $name);
        $this->metas = $meta;

        return $this;
    }
}
