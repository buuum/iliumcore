<?php

namespace Ilium\Dependency;


use Psr\Container\ContainerInterface;
use RouteF\RouteCollection;

class Router
{

    private $container;
    private $routes = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addRoutes(\Closure $closure)
    {
        $this->routes[] = $closure;
    }

    public function get()
    {
        $router = new RouteCollection($this->container, [
            'cacheFile'     => __DIR__ . '/route2.cache',
            'cacheDisabled' => true
        ]);

        $router->initRoutes(function (RouteCollection $router) {
            foreach ($this->routes as $route) {
                $route($router);
            }
        });

        return $router;
    }


}