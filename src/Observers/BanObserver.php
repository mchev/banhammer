<?php

namespace Mchev\Banhammer\Observers;

use Illuminate\Support\Facades\Cache;
use Mchev\Banhammer\Events\ModelWasBanned;
use Mchev\Banhammer\Events\ModelWasUnbanned;
use Mchev\Banhammer\IP;
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
        $this->updateCachedIps($ban);
    }

    public function deleted(Ban $ban): void
    {
        event(new ModelWasUnbanned($ban->bannable()));
        $this->updateCachedIps($ban);
    }

    public function updateCachedIps(Ban $ban): void
    {
        if ($ban->ip) {
            Cache::put('banned-ips', IP::banned()->pluck('ip')->unique()->toArray());
        }
    }
}
