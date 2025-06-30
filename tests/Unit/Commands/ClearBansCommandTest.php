<?php

namespace Mchev\Banhammer\Tests\Unit\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mchev\Banhammer\Banhammer;
use Mchev\Banhammer\Tests\TestCase;

class ClearBansCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_clears_bans_and_outputs_message(): void
    {
        // Mock Banhammer::clear
        \Mockery::mock('alias:'.Banhammer::class)
            ->shouldReceive('clear')
            ->once();

        $this->artisan('banhammer:clear')
            ->expectsOutput('All expired bans have been permanently deleted.')
            ->assertExitCode(0);
    }
}
