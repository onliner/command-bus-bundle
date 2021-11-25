<?php

declare(strict_types=1);

namespace Onliner\CommandBusBundle\DependencyInjection\Compiler;

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ExtensionPass extends AbstractServicePass
{
    private const TAG = 'onliner.commandbus.extension';

    public function process(ContainerBuilder $container): void
    {
        $services = $this->findServices($container, self::TAG, Extension::class);
        $builder = $container->getDefinition(Builder::class);

        foreach ($services as $id => $tags) {
            $builder->addMethodCall('use', [new Reference($id)]);
        }
    }
}
