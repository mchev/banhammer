<?php

namespace Mchev\Banhammer\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mchev\Banhammer\Tests\TestCase;

class LogoutBannedMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_banned_user_logs_out_and_throws(): void
    {
        $this->startSession();
        $middleware = $this->getMockBuilder(\Mchev\Banhammer\Middleware\LogoutBanned::class)
            ->onlyMethods(['getBannedIPsFromCache'])
            ->getMock();
        $middleware->expects($this->once())
            ->method('getBannedIPsFromCache')
            ->willReturn([]);
        $user = new class
        {
            public function isBanned()
            {
                return true;
            }
        };
        $request = \Illuminate\Http\Request::create('/');
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession(app('session.store'));
        \Illuminate\Support\Facades\Auth::shouldReceive('logout')->once();
        $this->expectException(\Mchev\Banhammer\Exceptions\BanhammerException::class);
        $middleware->handle($request, fn ($req) => $req);
    }

    public function test_banned_ip_throws(): void
    {
        $this->startSession();
        $middleware = $this->getMockBuilder(\Mchev\Banhammer\Middleware\LogoutBanned::class)
            ->onlyMethods(['getBannedIPsFromCache'])
            ->getMock();
        $middleware->expects($this->once())
            ->method('getBannedIPsFromCache')
            ->willReturn(['1.2.3.4']);
        $user = new class
        {
            public function isBanned()
            {
                return false;
            }
        };
        $request = \Illuminate\Http\Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '1.2.3.4']);
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession(app('session.store'));
        $this->expectException(\Mchev\Banhammer\Exceptions\BanhammerException::class);
        $middleware->handle($request, fn ($req) => $req);
    }

    public function test_non_banned_user_and_ip_passes_through(): void
    {
        $this->startSession();
        $middleware = $this->getMockBuilder(\Mchev\Banhammer\Middleware\LogoutBanned::class)
            ->onlyMethods(['getBannedIPsFromCache'])
            ->getMock();
        $middleware->expects($this->once())
            ->method('getBannedIPsFromCache')
            ->willReturn(['1.2.3.4']);
        $user = new class
        {
            public function isBanned()
            {
                return false;
            }
        };
        $request = \Illuminate\Http\Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '5.6.7.8']);
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession(app('session.store'));
        $response = $middleware->handle($request, fn ($req) => response('ok'));
        $this->assertSame('ok', $response->getContent());
    }
}
