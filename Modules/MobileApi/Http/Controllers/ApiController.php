<?php

namespace Modules\MobileApi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

abstract class ApiController extends Controller
{
    protected function error(string $code, string $message, int $status, array $errors = []): JsonResponse
    {
        $body = ['message' => $message, 'code' => $code];
        if ($errors) {
            $body['errors'] = $errors;
        }
        return response()->json($body, $status);
    }
}
