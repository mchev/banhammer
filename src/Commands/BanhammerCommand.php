<?php

namespace Mchev\Banhammer\Commands;

use Illuminate\Console\Command;
use Mchev\Banhammer\Banhammer;

class BanhammerCommand extends Command
{
    public $signature = 'banhammer:clear';

    public $description = 'Permanently delete all the expired bans';

    public function handle(): int
    {
        Banhammer::clear();
        $this->info('All expired bans have been deleted.');

        return self::SUCCESS;
    }
}
