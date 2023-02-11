<?php

namespace Mchev\Banhammer\Events;

use Mchev\Banhammer\Models\Ban;
use Illuminate\Contracts\Queue\ShouldQueue;

class ModelWasBanned implements ShouldQueue
{
    public function __construct(public $model, public Ban $ban)
    {
    }
}
