<?php

namespace App\Middleware;

/**
 * Middleware Manager
 * This class manages the execution of middleware
 */
class MiddlewareManager
{
    /**
     * Registered middleware
     * @var array
     */
    private array $middleware = [];
    
    /**
     * Register middleware
     * @param string $name Middleware name
     * @param string $class Middleware class
     */
    public function register(string $name, string $class): void
    {
        $this->middleware[$name] = $class;
    }
    
    /**
     * Run middleware
     * @param array $middlewareNames Names of middleware to run
     * @param array $params Parameters to pass to middleware
     * @return bool True if all middleware passes, false otherwise
     */
    public function run(array $middlewareNames, array $params = []): bool
    {
        foreach ($middlewareNames as $name) {
            if (isset($this->middleware[$name])) {
                $class = $this->middleware[$name];
                $middleware = new $class();
                
                if (!$middleware->handle($params)) {
                    return false;
                }
            }
        }
        
        return true;
    }
}