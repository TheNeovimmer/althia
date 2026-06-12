<?php
namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middleware = [];

    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->routes['GET'][] = ['path' => $path, 'handler' => $handler, 'middleware' => $middleware];
    }

    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->routes['POST'][] = ['path' => $path, 'handler' => $handler, 'middleware' => $middleware];
    }

    public function put(string $path, string $handler, array $middleware = []): void
    {
        $this->routes['PUT'][] = ['path' => $path, 'handler' => $handler, 'middleware' => $middleware];
    }

    public function delete(string $path, string $handler, array $middleware = []): void
    {
        $this->routes['DELETE'][] = ['path' => $path, 'handler' => $handler, 'middleware' => $middleware];
    }

    public function addMiddleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->middleware as $mw) {
            call_user_func($mw);
        }

        if (!isset($this->routes[$method])) {
            $this->notFound();
            return;
        }

        foreach ($this->routes[$method] as $route) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                foreach ($route['middleware'] as $mw) {
                    call_user_func($mw);
                }

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                [$controller, $method] = explode('@', $route['handler']);
                $controller = 'App\\Controllers\\' . $controller;
                $instance = new $controller();
                call_user_func_array([$instance, $method], array_values($params));
                return;
            }
        }

        $this->notFound();
    }

    private function notFound(): void
    {
        http_response_code(404);
        $controller = new \App\Controllers\HomeController();
        $controller->notFound();
    }
}
