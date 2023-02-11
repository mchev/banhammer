<?php

namespace Mchev\Banhammer\Observers;

use Mchev\Banhammer\Events\ModelWasBanned;
use Mchev\Banhammer\Events\ModelWasUnbanned;
use Mchev\Banhammer\Models\Ban;

class BanObserver
{
    public function creating(Ban $ban): void
    {
        $user = auth()->user();
        if ($user && is_null($ban->created_by_type) && is_null($ban->created_by_id)) {
            $ban->fill([
                'created_by_type' => $user->getMorphClass(),
                'created_by_id' => $user->getKey(),
            ]);
        }
    }

    public function created(Ban $ban): void
    {
        event(new ModelWasBanned($ban->bannable(), $ban));
    }

    public function deleted(Ban $ban): void
    {
        event(new ModelWasUnbanned($ban->bannable()));
    }
}
