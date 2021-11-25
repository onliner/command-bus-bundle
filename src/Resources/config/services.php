<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Dispatcher;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(Builder::class)

        ->set(Dispatcher::class)
            ->factory([service(Builder::class), 'build'])
    ;
};
