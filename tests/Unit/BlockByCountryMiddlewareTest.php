<?php

namespace Mchev\Banhammer\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mchev\Banhammer\Exceptions\BanhammerException;
use Mchev\Banhammer\Middleware\BlockByCountry;
use Mchev\Banhammer\Services\IpApiService;
use Mchev\Banhammer\Tests\TestCase;
use Mockery;

class BlockByCountryMiddlewareTest extends TestCase
{
    /** @var BlockByCountry */
    private $middleware;

    /** @var IpApiService|\Mockery\MockInterface */
    private $ipApiService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock for IpApiService
        $this->ipApiService = Mockery::mock(IpApiService::class);

        // Create an instance of the middleware with the mock service
        $this->middleware = new BlockByCountry($this->ipApiService);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Close Mockery expectations
        Mockery::close();
    }

    /** @test */
    public function it_allows_request_from_non_blocked_country()
    {
        // Given
        $ip = '100.42.30.255'; // IP from a country not in the blocked list

        // Setting configuration using Config facade
        config(['ban.block_by_country' => true]);
        config(['ban.blocked_countries' => ['FR', 'US']]);

        // Set up the mock behavior
        $this->ipApiService
            ->shouldReceive('getGeolocationData')
            ->andReturn(['status' => 'success', 'countryCode' => 'CA']);

        // Create a dummy request
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => $ip]);

        // When
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['status' => 'success']);
        });

        // Then
        $this->assertEquals('success', json_decode($response->getContent(), true)['status']);
    }

    /** @test */
    public function it_blocks_request_from_blocked_country()
    {
        // Given
        $ip = '100.42.30.255'; // IP from a blocked country

        // Setting configuration using Config facade
        config(['ban.block_by_country' => true]);
        config(['ban.blocked_countries' => ['FR', 'US']]);

        // Set up the mock behavior
        $this->ipApiService
            ->shouldReceive('getGeolocationData')
            ->andReturn(['status' => 'success', 'countryCode' => 'US']);

        // Create a dummy request
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => $ip]);

        // When
        $this->expectException(BanhammerException::class);
        $this->middleware->handle($request, function ($req) {
            // Dummy callback, as we are not actually calling the next middleware or endpoint
        });
    }

    /** @test */
    public function it_allows_request_when_country_check_fails()
    {
        // Given
        $ip = '100.42.30.255'; // IP from a country not in the blocked list

        // Setting configuration using Config facade
        config(['ban.block_by_country' => true]);
        config(['ban.blocked_countries' => ['FR', 'US']]);

        // Set up the mock behavior
        $this->ipApiService
            ->shouldReceive('getGeolocationData')
            ->andReturn(['status' => 'fail', 'message' => 'Failed to get geolocation']);

        // Create a dummy request
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => $ip]);

        // When
        $response = $this->middleware->handle($request, function ($req) {
            return response()->json(['status' => 'success']);
        });

        // Then
        $this->assertEquals('success', json_decode($response->getContent(), true)['status']);
    }

    /** @test */
    public function it_allows_request_when_cache_is_present()
    {
        // Given
        $ip = '100.42.30.255'; // IP from a country not in the blocked list

        // Setting configuration using Config facade
        config(['ban.block_by_country' => true]);
        config(['ban.blocked_countries' => ['FR', 'US']]);

        // Set up the mock behavior
        $this->ipApiService
            ->shouldReceive('getGeolocationData')
            ->andReturn(['status' => 'success', 'countryCode' => 'CA']);

        // Set up cache
        Cache::put('banhammer_'.$ip, 'blocked', now()->addMinutes(config('ban.cache_duration')));

        // Create a dummy request
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => $ip]);

        // When
        $this->expectException(BanhammerException::class);
        $this->middleware->handle($request, function ($req) {
            // Dummy callback, as we are not actually calling the next middleware or endpoint
        });
    }
}
