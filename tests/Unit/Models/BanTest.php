<?php

namespace Mchev\Banhammer\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mchev\Banhammer\Models\Ban;
use Mchev\Banhammer\Tests\TestCase;
use Carbon\Carbon;

class BanTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_at_attribute_casting(): void
    {
        $ban = new Ban();
        
        // Test with string date
        $ban->expired_at = '2024-01-01 00:00:00';
        $this->assertInstanceOf(Carbon::class, $ban->expired_at);
        
        // Test with Carbon instance
        $date = now();
        $ban->expired_at = $date;
        $this->assertEquals($date, $ban->expired_at);
        
        // Test with null
        $ban->expired_at = null;
        $this->assertNull($ban->expired_at);
    }

    public function test_permanent_scope(): void
    {
        Ban::create(['ip' => '1.1.1.1']); // Permanent ban
        Ban::create(['ip' => '2.2.2.2', 'expired_at' => now()->addDay()]);
        
        $this->assertCount(1, Ban::permanent()->get());
    }

    public function test_not_permanent_scope(): void
    {
        Ban::create(['ip' => '1.1.1.1']); // Permanent ban
        Ban::create(['ip' => '2.2.2.2', 'expired_at' => now()->addDay()]);
        
        $this->assertCount(1, Ban::notPermanent()->get());
    }

    public function test_expired_scope(): void
    {
        Ban::create(['ip' => '1.1.1.1', 'expired_at' => now()->subDay()]);
        Ban::create(['ip' => '2.2.2.2', 'expired_at' => now()->addDay()]);
        
        $this->assertCount(1, Ban::expired()->get());
    }

    public function test_not_expired_scope(): void
    {
        Ban::create(['ip' => '1.1.1.1']); // Permanent ban
        Ban::create(['ip' => '2.2.2.2', 'expired_at' => now()->subDay()]);
        Ban::create(['ip' => '3.3.3.3', 'expired_at' => now()->addDay()]);
        
        $this->assertCount(2, Ban::notExpired()->get());
    }

    public function test_where_meta_scope(): void
    {
        Ban::create([
            'ip' => '1.1.1.1',
            'metas' => ['reason' => 'spam', 'severity' => 'high']
        ]);
        Ban::create([
            'ip' => '2.2.2.2',
            'metas' => ['reason' => 'abuse', 'severity' => 'low']
        ]);
        
        $this->assertCount(1, Ban::whereMeta('reason', 'spam')->get());
        $this->assertCount(1, Ban::whereMeta('severity', 'high')->get());
    }

    public function test_has_meta(): void
    {
        $ban = Ban::create([
            'ip' => '1.1.1.1',
            'metas' => ['reason' => 'spam']
        ]);
        
        $this->assertTrue($ban->hasMeta('reason'));
        $this->assertFalse($ban->hasMeta('nonexistent'));
    }

    public function test_morphTo_relationships(): void
    {
        $ban = Ban::create([
            'ip' => '1.1.1.1',
            'created_by_type' => 'App\Models\User',
            'created_by_id' => 1
        ]);
        
        $this->assertEquals('App\Models\User', $ban->created_by_type);
        $this->assertEquals(1, $ban->created_by_id);
    }
} 