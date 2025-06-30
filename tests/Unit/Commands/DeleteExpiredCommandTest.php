<?php

namespace Mchev\Banhammer\Tests\Unit\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mchev\Banhammer\Banhammer;
use Mchev\Banhammer\Tests\TestCase;

class DeleteExpiredCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_deletes_expired_bans_and_outputs_message(): void
    {
        // Mock Banhammer::unbanExpired
        \Mockery::mock('alias:'.Banhammer::class)
            ->shouldReceive('unbanExpired')
            ->once();

        $this->artisan('banhammer:unban')
            ->expectsOutput('All expired bans have been deleted.')
            ->assertExitCode(0);
    }
}
