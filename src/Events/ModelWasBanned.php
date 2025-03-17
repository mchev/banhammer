<?php

namespace Mchev\Banhammer\Events;

use Illuminate\Contracts\Queue\ShouldQueue;

class ModelWasBanned implements ShouldQueue
{
    public function __construct(public $model, public $ban) {}
}
