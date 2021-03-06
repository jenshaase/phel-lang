<?php

declare(strict_types=1);

namespace Phel\Runtime\Exceptions;

use RuntimeException;

final class RuntimeNotInitializedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Runtime must first be initialized. Call Runtime::initialize()');
    }
}
