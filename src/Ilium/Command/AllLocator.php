<?php

namespace Ilium\Command;

use League\Tactician\Exception\MissingHandlerException;
use League\Tactician\Handler\Locator\HandlerLocator;
use Psr\Container\ContainerInterface;

class AllLocator implements HandlerLocator
{
    protected $container;

    protected $commandNameToHandlerMap = [];

    public function __construct(
        ContainerInterface $container,
        array $commandNameToHandlerMap = []
    ) {
        $this->container = $container;
        $this->addHandlers($commandNameToHandlerMap);
    }

    public function addHandler($handler, $commandName)
    {
        $this->commandNameToHandlerMap[$commandName] = $handler;
    }

    public function addHandlers(array $commandNameToHandlerMap)
    {
        foreach ($commandNameToHandlerMap as $commandName => $handler) {
            $this->addHandler($handler, $commandName);
        }
    }

    public function getHandlerForCommand($commandName)
    {
        if (!isset($this->commandNameToHandlerMap[$commandName])) {
            throw MissingHandlerException::forCommand($commandName);
        }

        $serviceId = $this->commandNameToHandlerMap[$commandName];

        if (is_string($serviceId)) {
            return $this->container->get($serviceId);
        }

        return $serviceId;
    }
}