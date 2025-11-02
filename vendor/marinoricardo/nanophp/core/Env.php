<?php
namespace Core;

class Env
{
    public static function load(string $path = __DIR__ . '/../.env'): void
    {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            [$name, $value] = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }

    public static function get(string $key, $default = null) {
        return $_ENV[$key] ?? $default;
    }

}