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
            (new IPBanned())->handle($request, function () {
                //
            });
        } catch (BanhammerException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }
}
