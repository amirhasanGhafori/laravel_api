<?php

namespace App\traits;


trait ApiResponse
{
    public function ok(string $message , $data)
    {
        return $this->success($message,$data,200);
    }

    protected function success(string $message, $data,$statusCode = 200)
    {
        return response()->json([
            'data'=>$data,
            'message' => $message,
            'status' => $statusCode
        ], $statusCode);
    }

    protected function error(string $message, $statusCode)
    {
        return response()->json([
            'message' => $message,
            'status' => $statusCode
        ], $statusCode);
    }
}
