<?php

declare(strict_types=1);

namespace Onliner\CommandBusBundle\DependencyInjection\Compiler;

use Onliner\CommandBus\Remote\Serializer;
use Onliner\CommandBusBundle\SerializerRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SerializerPass extends AbstractServicePass
{
    public const TAG = 'onliner.commandbus.serializer';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(SerializerRegistry::class)) {
            return;
        }

        $services = $this->findServices($container, self::TAG, Serializer::class);
        $registry = $container->getDefinition(SerializerRegistry::class);

        foreach ($services as $id => $tags) {
            foreach ($tags as $tag) {
                $registry->addMethodCall('add', [$tag['name'] ?? $id, new Reference($id)]);
            }
        }
    }
}
