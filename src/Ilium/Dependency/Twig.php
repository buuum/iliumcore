<?php

namespace Ilium\Dependency;

use League\Container\Container;
use RouteF\RouteCollection;

class Twig
{
    private $container;
    /**
     * @var RouteCollection
     */
    private $router;
    private $paths = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function addPath($path)
    {
        $this->addPaths((array)$path);
    }

    public function addPaths(array $paths)
    {
        $this->paths = array_merge($this->paths, $paths);
    }

    public function __invoke()
    {
        $this->router = $this->container->get('router');
        $loader = new \Twig_Loader_Filesystem($this->paths);

        $twig = new \Twig_Environment($loader, array(
            //'cache' => __DIR__ . '/templates/compilation_cache',
            //'debug' => $config->twig->debug,
        ));
        $twig->addExtension(new \Twig_Extension_Debug());

        $function = new \Twig_Function('route', function ($name, $options = []) {
            return $this->router->getUrl($name, $options);
        });

        $functione = new \Twig_Function('_e', function ($name) {
            return $name;
        });

        $twig->addFunction($function);
        $twig->addFunction($functione);

        return $twig;
    }

}