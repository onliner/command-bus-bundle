<?php

declare(strict_types=1);

namespace Onliner\CommandBusBundle\DependencyInjection\Compiler;

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Middleware;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MiddlewarePass extends AbstractServicePass
{
    private const TAG = 'onliner.commandbus.middleware';

    public function process(ContainerBuilder $container): void
    {
        $services = $this->findServices($container, self::TAG, Middleware::class);
        $builder = $container->getDefinition(Builder::class);

        foreach ($services as $id => $tags) {
            $builder->addMethodCall('middleware', [new Reference($id)]);
        }
    }
}
