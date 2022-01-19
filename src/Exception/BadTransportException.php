<?php

declare(strict_types=1);

namespace Onliner\CommandBusBundle\Exception;

use Onliner\CommandBus\Exception\CommandBusException;

class BadTransportException extends CommandBusException
{
    public function __construct(string $url)
    {
        parent::__construct(sprintf('Bad transport URL: %s.', $url));
    }
}
