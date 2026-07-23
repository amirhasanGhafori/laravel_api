<?php

namespace App\traits;


trait ApiResponse
{
    public function ok(string $message, $statusCode = 200)
    {
        return response([
            'message' => $message,
            'status' => $statusCode
        ]);
    }
}
