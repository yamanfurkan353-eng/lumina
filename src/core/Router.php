<?php
/**
 * Router Class
 * 
 * Handles URL routing, HTTP method dispatching, and controller invocation.
 */

namespace HotelMaster\Core;

class Router {
    private array $routes = [];
    private string $basePath = '/api';
    private \Closure $notFoundHandler;
    private \Closure $methodNotAllowedHandler;
    
    public function __construct() {
        // Default 404 handler
        $this->notFoundHandler = function() {
            return Response::error('Endpoint bulunamadı', 404);
        };
        
        // Default 405 handler
        $this->methodNotAllowedHandler = function() {
            return Response::error('HTTP metodu desteklenmiyor', 405);
        };
    }
    
    /**
     * Register a GET route
     */
    public function get(string $path, string $controller, string $method): self {
        return $this->addRoute('GET', $path, $controller, $method);
    }
    
    /**
     * Register a POST route
     */
    public function post(string $path, string $controller, string $method): self {
        return $this->addRoute('POST', $path, $controller, $method);
    }
    
    /**
     * Register a PUT route
     */
    public function put(string $path, string $controller, string $method): self {
        return $this->addRoute('PUT', $path, $controller, $method);
    }
    
    /**
     * Register a DELETE route
     */
    public function delete(string $path, string $controller, string $method): self {
        return $this->addRoute('DELETE', $path, $controller, $method);
    }
    
    /**
     * Register a PATCH route
     */
    public function patch(string $path, string $controller, string $method): self {
        return $this->addRoute('PATCH', $path, $controller, $method);
    }
    
    /**
     * Add a route
     */
    private function addRoute(string $httpMethod, string $path, string $controller, string $method): self {
        $pattern = $this->pathToRegex($path);
        $this->routes[] = [
            'method' => $httpMethod,
            'path' => $path,
            'pattern' => $pattern,
            'controller' => $controller,
            'handler' => $method
        ];
        return $this;
    }
    
    /**
     * Dispatch request to appropriate controller and method
     */
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path
        if (strpos($uri, '/api/') === 0) {
            $path = substr($uri, 4); // Remove '/api'
        } else {
            $path = $uri;
        }
        
        Logger::info("Route dispatch: {$method} {$path}");
        
        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $path, $matches)) {
                $this->executeRoute($route, $matches);
                return;
            }
        }
        
        // No route found
        http_response_code(404);
        echo json_encode(($this->notFoundHandler)());
        exit;
    }
    
    /**
     * Execute matched route
     */
    private function executeRoute(array $route, array $matches) {
        try {
            // Extract parameters from regex matches
            $params = [];
            foreach ($matches as $key => $value) {
                if (!is_numeric($key)) {
                    $params[$key] = $value;
                }
            }
            
            // Instantiate controller and call method
            $controllerClass = 'HotelMaster\\Controllers\\' . $route['controller'];
            
            if (!class_exists($controllerClass)) {
                throw new \Exception("Controller not found: {$controllerClass}");
            }
            
            $controller = new $controllerClass();
            $method = $route['handler'];
            
            if (!method_exists($controller, $method)) {
                throw new \Exception("Method not found: {$method} in {$controllerClass}");
            }
            
            // Call controller method
            $response = $controller->$method($params);
            
            // Send response
            http_response_code($response['status_code'] ?? 200);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response['data'] ?? $response);
            
        } catch (\Exception $e) {
            Logger::error("Route execution error: {$e->getMessage()}", [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            http_response_code(500);
            echo json_encode(Response::error('İç sunucu hatası', 500));
        }
        
        exit;
    }
    
    /**
     * Convert path to regex pattern
     * Converts /users/{id} to /users/(?P<id>\d+)
     */
    private function pathToRegex(string $path): string {
        $pattern = '#^' . preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            function ($matches) {
                $param = $matches[1];
                // Default to numeric ID pattern
                if ($param === 'id') {
                    return '(?P<id>\d+)';
                }
                return '(?P<' . $param . '>[^/]+)';
            },
            $path
        ) . '$#';
        
        return $pattern;
    }
    
    /**
     * Generate URL for a route
     */
    public function url(string $path, array $params = []): string {
        $url = $path;
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        return BASE_URL . $url;
    }
    
    /**
     * Set 404 handler
     */
    public function setNotFoundHandler(\Closure $handler): self {
        $this->notFoundHandler = $handler;
        return $this;
    }
    
    /**
     * Set 405 handler
     */
    public function setMethodNotAllowedHandler(\Closure $handler): self {
        $this->methodNotAllowedHandler = $handler;
        return $this;
    }
}
