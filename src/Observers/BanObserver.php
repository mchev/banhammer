<?php

namespace Mchev\Banhammer\Observers;

use Illuminate\Support\Facades\Cache;
use Mchev\Banhammer\Events\ModelWasBanned;
use Mchev\Banhammer\Events\ModelWasUnbanned;

class BanObserver
{
    public function creating($ban): void
    {
        $user = auth()->user();
        if ($user && is_null($ban->created_by_type) && is_null($ban->created_by_id)) {
            $ban->fill([
                'created_by_type' => $user->getMorphClass(),
                'created_by_id' => $user->getKey(),
            ]);
        }
    }

    public function created($ban): void
    {
        event(new ModelWasBanned($ban->bannable(), $ban));
        $this->updateCachedIps($ban);
    }

    public function deleted($ban): void
    {
        event(new ModelWasUnbanned($ban->bannable()));
        $this->updateCachedIps($ban);
    }

    public function updateCachedIps($ban): void
    {
        if ($ban->ip) {
            Cache::forget('banned-ips');
            Cache::forget('banned-ips-with-expiration');
        }
    }
}
