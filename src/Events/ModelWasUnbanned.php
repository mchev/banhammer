<?php

namespace Mchev\Banhammer\Events;

use Illuminate\Contracts\Queue\ShouldQueue;

class ModelWasUnbanned implements ShouldQueue
{
    public function __construct(public $model)
    {
    }
}
