<?php

namespace Ilium\Dependency;

interface ErrorHandlerInterface
{
    public function parseError($errtype, $errno, $errmsg, $filename, $linenum);
}