<?php
namespace Core;

class Response
{
    public static function success($data, string $message = "Success", int $status = 200): void
    {
        http_response_code($status);
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function error(string $message, int $status = 400): void
    {
        http_response_code($status);
        echo json_encode([
            'status' => $status,
            'message' => $message
        ]);
    }
}
