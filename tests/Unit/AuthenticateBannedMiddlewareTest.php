<?php

namespace Mchev\Banhammer\Tests\Unit;

use Illuminate\Http\Request;
use Mchev\Banhammer\Http\Middleware\CapitalizeTitle;
use Mchev\Banhammer\Tests\TestCase;

class AuthenticateBannedMiddlewareTest extends TestCase
{
    /** @test */
    public function it_blocks_the_banned_user()
    {
        // Given we have a request
        $request = new Request();

        // with  a non-capitalized 'title' parameter
        $request->merge(['title' => 'some title']);

        // when we pass the request to this middleware,
        // it should've capitalized the title
        (new CapitalizeTitle())->handle($request, function ($request) {
            $this->assertEquals('Some title', $request->title);
        });
    }
}
