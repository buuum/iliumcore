<?php

namespace Ilium;

use Ilium\Dependency\CommandBus;
use Ilium\Dependency\Config;
use Ilium\Dependency\Console;
use Ilium\Dependency\Doctrine;
use Ilium\Dependency\ErrorHandler;
use Ilium\Dependency\QueryBus;
use Ilium\Dependency\Router;
use Ilium\Dependency\Twig;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManager;
use League\Container\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class Ilium
{

    /**
     * @var Container
     */
    public $container;
    /**
     * @var Config
     */
    public $config;
    /**
     * @var Twig
     */
    public $twig;
    /**
     * @var Doctrine
     */
    public $doctrine;
    /**
     * @var CommandBus
     */
    public $command_bus;
    /**
     * @var QueryBus
     */
    public $query_bus;
    /**
     * @var Router
     */
    public $router;
    /**
     * @var ErrorHandler
     */
    public $error_handler;
    /**
     * @var Console
     */
    public $console;

    public function __construct()
    {
        $this->container = $app = new Container();

        $app->share(Request::class, Request::createFromGlobals());

        $this->config = new Config($app->get(Request::class)->getUri());

        $app->share(Session::class, [$this, 'getSession']);

        $this->twig = new Twig($this->container);
        $this->doctrine = new Doctrine(
            $this->config->get('db'),
            $this->config->get('root_path') . '/Proxies',
            $this->config->get('development')
        );
        $this->command_bus = new CommandBus($this->container);
        $this->query_bus = new QueryBus($this->container);
        $this->router = new Router($this->container);
        $this->error_handler = new ErrorHandler($this->config->get('development'));
        $this->console = new Console($this->container);

        $this->iniModules();

        $app->share('command_bus', function () {
            return ($this->command_bus)();
        });

        $app->share('query_bus', function () {
            return ($this->query_bus)();
        });

        $app->share('twig', function () {
            return ($this->twig)();
        });

        $app->share(DebugStack::class, DebugStack::class);
        $app->share(EntityManager::class, function () {
            return ($this->doctrine)();
        });

        $app->share('router', function () {
            return ($this->router)();
        });

        $app->share('console', function () {
            return ($this->console)();
        });

    }

    protected function iniModules()
    {

        $root = $this->config->get('root_path');
        if ($bootstraps = $this->config->get('bootstraps')) {
            $app = $this;
            foreach ($bootstraps as $module) {
                require_once $root . DIRECTORY_SEPARATOR . $module;
            }
        }
    }

    protected function getSession()
    {
        $session = new Session();
        if ($this->config->get('scope.config.session_name')) {
            $session->setName($this->config->get('scope.config.session_name'));
        }
        return $session;
    }

    public function dispatch()
    {
        $request = $this->container->get(Request::class);
        return $this->container->get('router')->dispatch(
            $request->getMethod(),
            $request->getScheme() . '://' . $request->getHost() . $request->getPathInfo()
        );
    }

}