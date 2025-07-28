<?php

namespace Mchev\Banhammer\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mchev\Banhammer\IP;
use Mchev\Banhammer\Models\Ban;
use Mchev\Banhammer\Tests\TestCase;

class IPBanTest extends TestCase
{
    use RefreshDatabase;

    public $ip = '127.0.0.1';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Cache::forget('banned-ips');
        Cache::forget('banned-ips-with-expiration');
    }

    public function test_ip_ban_can_be_created(): void
    {
        IP::ban($this->ip);
        $this->assertDatabaseCount(config('ban.table'), 1);
    }

    public function test_multiple_ip_are_banned(): void
    {
        IP::ban([$this->ip, '8.8.8.8', '4.4.4.4']);
        $this->assertDatabaseCount(config('ban.table'), 3);
    }

    public function test_duplicate_ip_ban_is_prevented(): void
    {
        IP::ban($this->ip);
        IP::ban($this->ip); // Attempt to ban same IP again
        $this->assertDatabaseCount(config('ban.table'), 1);
    }

    public function test_ip_ban_with_expiration(): void
    {
        $expiration = now()->addDay();
        IP::ban($this->ip, [], $expiration);

        $ban = Ban::where('ip', $this->ip)->first();
        $this->assertSame($expiration->format('Y-m-d H:i:s'), $ban->expired_at->format('Y-m-d H:i:s'));
    }

    public function test_ip_ban_with_metas(): void
    {
        $metas = ['reason' => 'spam', 'severity' => 'high'];
        IP::ban($this->ip, $metas);

        $ban = Ban::where('ip', $this->ip)->first();
        $this->assertSame($metas, $ban->metas);
    }

    public function test_ip_is_unbanned(): void
    {
        IP::ban($this->ip);
        IP::unban($this->ip);
        $this->assertDatabaseMissing(config('ban.table'), ['ip' => $this->ip, 'deleted_at' => null]);
    }

    public function test_multiple_ips_are_unbanned(): void
    {
        $ips = [$this->ip, '8.8.8.8', '4.4.4.4'];
        IP::ban($ips);
        IP::unban($ips);
        foreach ($ips as $ip) {
            $this->assertDatabaseMissing(config('ban.table'), ['ip' => $ip, 'deleted_at' => null]);
        }
    }

    public function test_is_banned_check(): void
    {
        IP::ban($this->ip);
        $this->assertTrue(IP::isBanned($this->ip));

        IP::unban($this->ip);
        $this->assertFalse(IP::isBanned($this->ip));
    }

    public function test_expired_ban_is_not_considered_banned(): void
    {
        IP::ban($this->ip, [], now()->subDay());
        $this->assertFalse(IP::isBanned($this->ip));
    }

    public function test_get_banned_ips_from_cache(): void
    {
        // Test when cache exists
        $cachedIps = ['1.1.1.1', '2.2.2.2'];
        Cache::put('banned-ips', $cachedIps);
        $this->assertSame($cachedIps, IP::getBannedIPsFromCache());

        // Test when cache doesn't exist
        Cache::forget('banned-ips');
        IP::ban(['3.3.3.3', '4.4.4.4']);
        $this->assertCount(2, IP::getBannedIPsFromCache());
    }

    public function test_cache_filters_out_expired_bans(): void
    {
        // Ban an IP with expiration in the past
        IP::ban('1.1.1.1', [], now()->subDay());

        // Ban another IP without expiration
        IP::ban('2.2.2.2');

        // The cache should automatically filter out expired IPs
        $bannedIps = IP::getBannedIPsFromCache();

        $this->assertNotContains('1.1.1.1', $bannedIps);
        $this->assertContains('2.2.2.2', $bannedIps);
        $this->assertCount(1, $bannedIps);
    }

    public function test_is_banned_uses_smart_cache(): void
    {
        // Ban an IP with expiration in the future
        IP::ban('1.1.1.1', [], now()->addDay());

        // Ban another IP with expiration in the past
        IP::ban('2.2.2.2', [], now()->subDay());

        // Should return true for future expiration, false for past
        $this->assertTrue(IP::isBanned('1.1.1.1'));
        $this->assertFalse(IP::isBanned('2.2.2.2'));
    }

    public function test_permanent_ban_works_with_cache(): void
    {
        // Ban an IP permanently (no expiration)
        IP::ban('1.1.1.1');

        $this->assertTrue(IP::isBanned('1.1.1.1'));
    }
}
