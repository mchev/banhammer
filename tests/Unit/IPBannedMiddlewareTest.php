<?php

namespace Mchev\Banhammer\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mchev\Banhammer\Exceptions\BanhammerException;
use Mchev\Banhammer\IP;
use Mchev\Banhammer\Middleware\IPBanned;
use Mchev\Banhammer\Tests\TestCase;

class IPBannedMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_blocks_the_banned_ip()
    {
        // $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $ip = '127.0.0.1';

        IP::ban([$ip]);

        $request = Request::create(config('app.url').'500', 'GET', [], [], [], ['REMOTE_ADDR' => $ip]);

        try {
            (new IPBanned)->handle($request, function () {
                //
            });
        } catch (BanhammerException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_logs_and_rethrows_on_exception(): void
    {
        $middleware = $this->getMockBuilder(\Mchev\Banhammer\Middleware\IPBanned::class)
            ->onlyMethods(['getBannedIPsFromCache'])
            ->getMock();
        $middleware->expects($this->once())
            ->method('getBannedIPsFromCache')
            ->will($this->throwException(new \Exception('cache error')));
        $request = \Illuminate\Http\Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '1.2.3.4']);
        \Illuminate\Support\Facades\Log::shouldReceive('error')
            ->once()
            ->withArgs(fn ($msg, $context) => str_contains($msg, 'IPBanned Middleware Exception: cache error') && isset($context['exception']));
        $this->expectException(\Exception::class);
        $middleware->handle($request, fn ($req) => response('ok'));
    }

    public function test_non_banned_ip_passes_through(): void
    {
        $middleware = $this->getMockBuilder(\Mchev\Banhammer\Middleware\IPBanned::class)
            ->onlyMethods(['getBannedIPsFromCache'])
            ->getMock();
        $middleware->expects($this->once())
            ->method('getBannedIPsFromCache')
            ->willReturn(['1.2.3.4']);
        $request = \Illuminate\Http\Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '5.6.7.8']);
        $response = $middleware->handle($request, fn ($req) => response('ok'));
        $this->assertSame('ok', $response->getContent());
    }
}
