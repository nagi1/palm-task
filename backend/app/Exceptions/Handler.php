<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    public function register(): void
    {
        $this->renderable(function (ProductNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => $e->getMessage() ?: 'Product not found',
                ], 404);
            }
        });

        $this->renderable(function (BlockedRequestException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'blocked',
                    'message' => $e->getMessage() ?: 'Request blocked',
                ], 429);
            }
        });
    }
}
