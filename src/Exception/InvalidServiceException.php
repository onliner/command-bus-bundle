<?php

declare(strict_types=1);

namespace Onliner\CommandBusBundle\Exception;

use InvalidArgumentException;

class InvalidServiceException extends InvalidArgumentException
{
    public function __construct(string $id, string $tag, string $class)
    {
        parent::__construct(sprintf('The service "%s" tagged "%s" must be an instance of "%s".', $id, $tag, $class));
    }
}
