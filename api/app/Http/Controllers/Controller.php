<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * @param Exception $e
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function handleException(Exception $e, string $message, int $code = 500): JsonResponse
    {
        Log::channel('stderr')->error($e->getTraceAsString());

        $data = [
            'success' => false,
            'details' => $message
        ];
        return response()->json($data, $code);
    }
}
