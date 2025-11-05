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
        return ['message' => $message, 'status' => $code];
    }

    // Renderiza view sem dados

    /**
     * @throws \Exception
     */
    protected function view(string $path): void
    {
        $basePath = dirname(__DIR__, 2);
        $file = $basePath . '/App/Views/' . $path . '.php';

        if (!file_exists($file)) {
            throw new \Exception("View '$path' não encontrada! ($file)");
        }

        include $file; // <-- Apenas inclui (não dá return)
    }

}
