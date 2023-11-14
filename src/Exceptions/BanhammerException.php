<?php

namespace Mchev\Banhammer\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BanhammerException extends HttpException
{
    public function __construct($message = null, Exception $previous = null, $code = 0)
    {
        parent::__construct(403, $message, $previous, [], $code);
    }

    /**
     * Report or log an exception.
     */
    public function report(): void
    {
        Log::error("Banhammer Exception: {$this->getMessage()}");
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): Response
    {
        return (config('ban.fallback_url'))
            ? redirect(config('ban.fallback_url'))
            : abort(403, $this->getMessage());
    }
}
