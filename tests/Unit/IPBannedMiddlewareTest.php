<?php

namespace Mchev\Banhammer\Tests\Unit;

use Illuminate\Http\Request;
use Mchev\Banhammer\Banhammer;
use Mchev\Banhammer\Middleware\IPBanned;
use Mchev\Banhammer\Tests\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class IPBannedMiddlewareTest extends TestCase
{
    /** @test */
    public function it_blocks_the_banned_ip()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        Banhammer::ban(['127.0.0.2']);

        $request = Request::create(config('app.url').'500', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.2']);
        $middleware = new IPBanned();
        $expectedStatusCode = 403;
        try {
            $middleware->handle($request, function () {
            });
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }
}
