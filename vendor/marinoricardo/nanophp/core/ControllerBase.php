<?php
namespace Core;


abstract class ControllerBase
{
    protected function success($data): array
    {
        return ['data' => $data];
    }

    protected function info(string $message, int $code = 201): array
    {
        return ['message' => $message, 'status' => $code];
    }

    protected function error(string $message, int $code = 404): array
    {
        return ['error' => $message, 'status' => $code];
    }
}
