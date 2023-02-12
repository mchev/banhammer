<?php

namespace Mchev\Banhammer;

use Mchev\Banhammer\Models\Ban;

class Banhammer
{
    public static function unbanExpired(): void
    {
        Ban::expired()->delete();
    }

    public static function clear(): void
    {
        Ban::onlyTrashed()->forceDelete();
    }
}
