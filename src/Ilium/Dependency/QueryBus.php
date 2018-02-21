<?php

namespace Ilium\Dependency;

use Ilium\Command\AllLocator;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\Plugins\LockingMiddleware;
use Psr\Container\ContainerInterface;

class QueryBus
{
    private $container;
    private $queries = [];
    //private $commands_middlewares = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addQueries(array $commands)
    {
        $this->queries = array_merge($this->queries, $commands);
    }

    public function get()
    {
        $locator = new AllLocator(
            $this->container,
            $this->queries
        );

        $commandHandlers = new CommandHandlerMiddleware(
            new ClassNameExtractor(),
            $locator,
            new HandleInflector()
        );

        $command_bus = new \League\Tactician\CommandBus([
            new LockingMiddleware(),
            $commandHandlers
        ]);

        return $command_bus;
    }

}