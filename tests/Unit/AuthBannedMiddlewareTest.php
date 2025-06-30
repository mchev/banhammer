<?php

namespace Mchev\Banhammer\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mchev\Banhammer\Exceptions\BanhammerException;
use Mchev\Banhammer\Middleware\AuthBanned;
use Mchev\Banhammer\Tests\TestCase;

class AuthBannedMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_banned_user_throws_exception(): void
    {
        $middleware = new AuthBanned;
        $request = Request::create('/');
        $user = new class
        {
            public function isBanned()
            {
                return true;
            }
        };
        $request->setUserResolver(fn () => $user);
        $this->expectException(BanhammerException::class);
        $middleware->handle($request, fn ($req) => $req);
    }

    public function test_non_banned_user_passes_through(): void
    {
        $middleware = new AuthBanned;
        $request = Request::create('/');
        $user = new class
        {
            public function isBanned()
            {
                return false;
            }
        };
        $request->setUserResolver(fn () => $user);
        $response = $middleware->handle($request, fn ($req) => response('ok'));
        $this->assertSame('ok', $response->getContent());
    }

    public function test_guest_passes_through(): void
    {
        $middleware = new AuthBanned;
        $request = Request::create('/');
        $request->setUserResolver(fn () => null);
        $response = $middleware->handle($request, fn ($req) => response('ok'));
        $this->assertSame('ok', $response->getContent());
    }
}
