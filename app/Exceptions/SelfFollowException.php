<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class SelfFollowException extends Exception
{
    public function render($request): JsonResponse
    {
        return response()->json([
            'message' => 'Você não pode seguir a si mesmo.'
        ], 403);
    }
}
