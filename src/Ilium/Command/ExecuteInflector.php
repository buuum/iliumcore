<?php

namespace Ilium\Command;

use League\Tactician\Handler\MethodNameInflector\MethodNameInflector;

class ExecuteInflector implements MethodNameInflector
{
    public function inflect($command, $commandHandler)
    {
        return 'execute';
    }
}