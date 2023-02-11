<?php

namespace Mchev\Banhammer\Commands;

use Illuminate\Console\Command;
use Mchev\Banhammer\Banhammer;

class DeleteExpired extends Command
{
    public $signature = 'banhammer:unban';

    public $description = 'Delete all the expired bans';

    public function handle(): int
    {
        Banhammer::unbanExpired();
        $this->info('All expired bans have been deleted.');

        return self::SUCCESS;
    }
}
