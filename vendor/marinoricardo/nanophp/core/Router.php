<?php
namespace Core;

class Router
{
    private array $routes = [];

    public function add(string $method, string $path, callable|string $callback): void
    {
        $method = strtoupper($method);
        $path = rtrim($path, '/') ?: '/'; // normaliza a rota
        $this->routes[$method][$path] = $callback;
    }

    public function dispatch(string $method, string $uri): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $method = strtoupper($method);
        $path = rtrim(parse_url($uri, PHP_URL_PATH), '/') ?: '/';
        $request = new Request();

        $methodsAllowed = [];

        // Verifica rotas exatas primeiro
        if (isset($this->routes[$method][$path])) {
            $this->execute($this->routes[$method][$path], $request);
            return;
        }

        // Suporte a rotas com parâmetros
        foreach ($this->routes as $m => $routes) {
            foreach ($routes as $route => $callback) {
                $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<\1>[^/]+)', $route);
                $pattern = '#^' . $pattern . '$#';
                if (preg_match($pattern, $path, $matches)) {
                    if ($m === $method) {
                        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                        $this->execute($callback, $request, ...array_values($params));
                        return;
                    } else {
                        $methodsAllowed[] = $m;
                    }
                }
            }
        }

        // Se o caminho existe, mas não para este método
        if (!empty($methodsAllowed)) {
            http_response_code(405);
            echo json_encode([
                'error' => 'Method Not Allowed',
                'allowed_methods' => $methodsAllowed
            ]);
            return;
        }

        // Se não encontrou nada
        http_response_code(404);
        echo json_encode([
            'error' => 'Rota não encontrada',
            'method' => $method,
            'uri' => $path
        ]);
    }


    private function execute(callable|string $callback, Request $request, ...$params): void
    {
        $result = null;

        // Se o callback for "Controller@method"
        if (is_string($callback) && str_contains($callback, '@')) {
            [$controllerClass, $method] = explode('@', $callback);
            if (!class_exists($controllerClass)) {
                http_response_code(500);
                echo json_encode(['error' => "Controller $controllerClass não encontrado"]);
                return;
            }
            $controller = new $controllerClass();
            $result = $controller->$method($request, ...$params);
        } else {
            // callback direto (função anônima)
            $result = $callback($request, ...$params);
        }

        // Se o callback retornou algo, envia via Response
        if ($result !== null) {
            echo json_encode($result);
        }
    }

    public function get(string $path, callable|string $callback): void
    {
        $this->add('GET', $path, $callback);
    }

    public function post(string $path, callable|string $callback): void
    {
        $this->add('POST', $path, $callback);
    }

    public function put(string $path, callable|string $callback): void
    {
        $this->add('PUT', $path, $callback);
    }

    public function delete(string $path, callable|string $callback): void
    {
        $this->add('DELETE', $path, $callback);
    }
}
