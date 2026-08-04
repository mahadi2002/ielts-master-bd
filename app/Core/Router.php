<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, string $handler, array $middleware): void
    {
        $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', rtrim($path, '/') ?: '/');
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'regex' => '#^' . $pattern . '$#u',
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): void
    {
        $path = $request->path;

        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) {
                continue;
            }
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            $params = array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
            $request->setParams($params);

            foreach ($route['middleware'] as $middlewareClass) {
                $result = (new $middlewareClass())->handle($request);
                if ($result === false) {
                    return; // middleware already sent a response
                }
            }

            [$controllerName, $method] = explode('@', $route['handler']);
            $controllerClass = 'App\\Controllers\\' . $controllerName;
            $controller = new $controllerClass();
            $controller->$method($request);
            return;
        }

        Response::notFound();
    }
}
