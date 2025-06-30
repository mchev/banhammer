<?php

namespace Mchev\Banhammer\Tests\Unit\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mchev\Banhammer\Exceptions\BanhammerException;
use Mchev\Banhammer\Tests\TestCase;

class BanhammerExceptionTest extends TestCase
{
    public function test_constructor_sets_status_and_message(): void
    {
        $e = new BanhammerException('forbidden', null, 123);
        $this->assertSame(403, $e->getStatusCode());
        $this->assertSame('forbidden', $e->getMessage());
        $this->assertSame(123, $e->getCode());
    }

    public function test_report_logs_error(): void
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(fn ($msg) => str_contains($msg, 'Banhammer Exception: test'));
        $e = new BanhammerException('test');
        $e->report();
    }

    public function test_render_redirects_if_fallback_url(): void
    {
        config(['ban.fallback_url' => '/banned']);
        $e = new BanhammerException('banned');
        $request = Request::create('/');
        $response = $e->render($request);
        $this->assertSame('http://localhost/banned', $response->headers->get('Location'));
    }

    public function test_render_aborts_with_403_if_no_fallback_url(): void
    {
        config(['ban.fallback_url' => null]);
        $e = new BanhammerException('banned');
        $request = Request::create('/');
        try {
            $e->render($request);
            $this->fail('Expected abort(403)');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $ex) {
            $this->assertSame(403, $ex->getStatusCode());
            $this->assertSame('banned', $ex->getMessage());
        }
    }
}
