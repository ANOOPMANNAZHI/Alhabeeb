<?php

namespace Modules\MobileApi\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionRenderer
{
    public static function wants(Request $request): bool
    {
        return $request->is('api/v1/*') || $request->is('api/v1');
    }

    public static function render(Request $request, Throwable $e): ?JsonResponse
    {
        return match (true) {
            $e instanceof ValidationException => response()->json(['message' => 'The given data was invalid.', 'code' => 'validation', 'errors' => $e->errors()], 422),
            $e instanceof AuthenticationException => response()->json(['message' => 'Unauthenticated.', 'code' => 'unauthenticated'], 401),
            $e instanceof AuthorizationException => response()->json(['message' => $e->getMessage() ?: 'Forbidden.', 'code' => 'forbidden'], 403),
            $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException => response()->json(['message' => 'Not found.', 'code' => 'not_found'], 404),
            $e instanceof \DomainException => response()->json(['message' => $e->getMessage(), 'code' => method_exists($e, 'code') ? $e->code() : 'invalid_state'], 409),
            default => null,
        };
    }
}
