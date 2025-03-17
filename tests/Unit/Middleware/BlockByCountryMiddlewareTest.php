<?php

namespace Mchev\Banhammer\Tests\Unit\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mchev\Banhammer\Exceptions\BanhammerException;
use Mchev\Banhammer\Middleware\BlockByCountry;
use Mchev\Banhammer\Services\IpApiService;
use Mchev\Banhammer\Tests\TestCase;
use Mockery;

class BlockByCountryMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private $middleware;
    private $ipApiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ipApiService = Mockery::mock(IpApiService::class);
        $this->middleware = new BlockByCountry($this->ipApiService);
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_middleware_skips_when_feature_disabled(): void
    {
        config(['ban.block_by_country' => false]);
        
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '1.1.1.1']);
        
        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });
        
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_middleware_allows_request_when_no_blocked_countries(): void
    {
        config(['ban.block_by_country' => true]);
        config(['ban.blocked_countries' => []]);
        
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '1.1.1.1']);
        
        // No need to mock API call when no countries are blocked
        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });
        
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_middleware_handles_api_error(): void
    {
        config(['ban.block_by_country' => true]);
        config(['ban.blocked_countries' => ['FR']]);
        
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '1.1.1.1']);
        
        $this->ipApiService
            ->shouldReceive('getGeolocationData')
            ->once()
            ->with('1.1.1.1')
            ->andReturn(['status' => 'fail', 'message' => 'API Error']);
            
        // The middleware should allow the request to pass through when API fails
        $next = function ($request) {
            return response('OK');
        };
        
        $response = $this->middleware->handle($request, $next);
        
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_middleware_blocks_request_from_blocked_country(): void
    {
        config(['ban.block_by_country' => true]);
        config(['ban.blocked_countries' => ['FR']]);
        
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '1.1.1.1']);
        
        $this->ipApiService
            ->shouldReceive('getGeolocationData')
            ->once()
            ->with('1.1.1.1')
            ->andReturn(['status' => 'success', 'countryCode' => 'FR']);

        $this->expectException(BanhammerException::class);
        $this->expectExceptionMessage(config('ban.messages.country'));
        
        $this->middleware->handle($request, function () {});
    }

    public function test_middleware_uses_cached_results(): void
    {
        config(['ban.block_by_country' => true]);
        config(['ban.blocked_countries' => ['FR']]);
        
        $ip = '1.1.1.1';
        Cache::put("country_$ip", 'FR', now()->addMinutes(config('ban.cache_duration', 120)));
        
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => $ip]);
        
        // Don't set any expectations on ipApiService - it shouldn't be called
        
        $this->expectException(BanhammerException::class);
        $this->expectExceptionMessage(config('ban.messages.country'));
        
        $this->middleware->handle($request, function () {});
    }

    public function test_middleware_allows_non_blocked_country(): void
    {
        config(['ban.block_by_country' => true]);
        config(['ban.blocked_countries' => ['FR']]);
        
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '1.1.1.1']);
        
        $this->ipApiService
            ->shouldReceive('getGeolocationData')
            ->once()
            ->with('1.1.1.1')
            ->andReturn(['status' => 'success', 'countryCode' => 'US']);

        $response = $this->middleware->handle($request, function ($req) {
            return response('OK');
        });
        
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_middleware_caches_results(): void
    {
        config(['ban.block_by_country' => true]);
        config(['ban.blocked_countries' => ['FR']]);
        config(['ban.cache_duration' => 120]); // Ensure cache duration is set
        
        $ip = '1.1.1.1';
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => $ip]);
        
        $this->ipApiService
            ->shouldReceive('getGeolocationData')
            ->once()
            ->with($ip)
            ->andReturn(['status' => 'success', 'countryCode' => 'US']);

        // Execute middleware
        $this->middleware->handle($request, function ($req) {
            return response('OK');
        });
        
        // Verify the cache
        $cacheKey = "country_$ip";
        $this->assertTrue(Cache::has($cacheKey), "Cache key '$cacheKey' not found");
        $this->assertEquals('US', Cache::get($cacheKey), "Cached country code doesn't match expected value");
    }

    public function test_middleware_uses_cached_country_code(): void
    {
        config(['ban.block_by_country' => true]);
        config(['ban.blocked_countries' => ['US']]);
        
        $ip = '1.1.1.1';
        Cache::put("country_$ip", 'US', now()->addMinutes(120));
        
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => $ip]);
        
        // The service should not be called since we're using cached value
        $this->ipApiService->shouldNotReceive('getGeolocationData');
        
        $this->expectException(BanhammerException::class);
        $this->expectExceptionMessage(config('ban.messages.country'));
        
        $this->middleware->handle($request, function () {});
    }
} 