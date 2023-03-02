<?php

namespace Mchev\Banhammer\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mchev\Banhammer\IP;
use Mchev\Banhammer\Models\Ban;
use Mchev\Banhammer\Tests\TestCase;

class IPBanTest extends TestCase
{
    use RefreshDatabase;

    public $ip = '127.0.0.1';

    public function test_ip_ban_can_be_created(): void
    {
        IP::ban($this->ip);
        $this->assertDatabaseCount(config('ban.table'), 1);
    }

    public function test_ip_is_banned(): void
    {
        IP::ban($this->ip);
        $ban = Ban::where('ip', $this->ip)->first();
        $this->assertNotSoftDeleted($ban);
    }

    public function test_ip_ban_with_metas(): void
    {
        IP::ban($this->ip, [
            'user_agent' => request()->header('user-agent'),
        ]);
        $ban = Ban::where('ip', $this->ip)->first();
        $this->assertTrue($ban->hasMeta('user_agent'));
    }

    public function test_ip_is_unbanned(): void
    {
        IP::ban($this->ip);
        $ban = Ban::where('ip', $this->ip)->first();
        IP::unban($this->ip);
        $this->assertSoftDeleted($ban);
    }

    public function test_multiple_ip_are_banned(): void
    {
        IP::ban([$this->ip, '8.8.8.8', '4.4.4.4']);
        $this->assertDatabaseCount(config('ban.table'), 3);
    }
}
