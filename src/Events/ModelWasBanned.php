<?php

namespace Mchev\Banhammer\Events;

use Illuminate\Contracts\Queue\ShouldQueue;
use Mchev\Banhammer\Models\Ban;

class ModelWasBanned implements ShouldQueue
{
    public function __construct(public $model, public Ban $ban)
    {
    }
}
