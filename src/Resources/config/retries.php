<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Onliner\CommandBus\Retry\RetryExtension;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(RetryExtension::class)
            ->tag('onliner.commandbus.extension')
    ;
};
