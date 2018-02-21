<?php

namespace Ilium\Dependency;

use League\Container\Container;
use Symfony\Component\Console\Application;

class Console
{

    private $commands = [];

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function get()
    {
        $console = new Application();
        foreach ($this->commands as $command) {
            $command = $this->container->has($command) ? $this->container->get($command) : new $command;
            $console->add($command);
        }
        return $console;
    }

    public function addCommand(string $command)
    {
        $this->commands[] = $command;
    }

}