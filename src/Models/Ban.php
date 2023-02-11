<?php

namespace Mchev\Banhammer\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
            set: fn (string|Carbon $value) => (! is_null($value) && ! $value instanceof Carbon) ? Carbon::parse($value) : $value,
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
}
