<?php

namespace Mchev\Banhammer\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Ban extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by_type',
        'created_by_id',
        'comment',
        'ip',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    protected function expiredAt(): Attribute
    {
        return Attribute::make(
            set: fn (null|string|Carbon $value) => (! is_null($value) && ! $value instanceof Carbon) ? Carbon::parse($value) : $value,
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

}
