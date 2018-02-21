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
    //private $commands_middlewares = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addCommands(array $commands)
    {
        $this->commands = array_merge($this->commands, $commands);
    }

    public function get()
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
        $command_bus_execution[] = $commandHandlers;

        $command_bus = new \League\Tactician\CommandBus($command_bus_execution);
        return $command_bus;
    }

}