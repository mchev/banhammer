<?php

namespace Mchev\Banhammer\Commands;

use Illuminate\Console\Command;

class BanhammerCommand extends Command
{
    public $signature = 'bans-for-laravel';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
