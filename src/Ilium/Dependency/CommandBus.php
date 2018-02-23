<?php

namespace Ilium\Dependency;

use Ilium\Command\AllLocator;
use Ilium\Command\ExecuteInflector;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Plugins\LockingMiddleware;
use Psr\Container\ContainerInterface;

class CommandBus
{
    private $container;
    private $commands = [];
    private $commands_middlewares = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addCommands(array $commands)
    {
        $this->commands = array_merge($this->commands, $commands);
    }

    public function addMiddleware($middleware)
    {
        $this->commands_middlewares[] = $middleware;
    }

    public function addMiddlewares(array $middlewares)
    {
        $this->commands_middlewares = array_merge($this->commands_middlewares, $middlewares);
    }

    public function __invoke()
    {
        $locator = new AllLocator(
            $this->container,
            $this->commands
        );

        $commandHandlers = new CommandHandlerMiddleware(
            new ClassNameExtractor(),
            $locator,
            new ExecuteInflector()
        );

        $command_bus_execution = [];
        $command_bus_execution[] = new LockingMiddleware();
        if (!empty($this->commands_middlewares)) {
            $reverse = array_reverse($this->commands_middlewares);
            foreach ($reverse as $middleware) {
                if (is_string($middleware)) {
                    $middleware = $this->container->get($middleware);
                }
                $command_bus_execution[] = $middleware;
            }
        }
        $command_bus_execution[] = $commandHandlers;

        $command_bus = new \League\Tactician\CommandBus($command_bus_execution);
        return $command_bus;
    }

}