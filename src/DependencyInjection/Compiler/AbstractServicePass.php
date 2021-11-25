<?php

namespace Onliner\CommandBusBundle\DependencyInjection\Compiler;

use Generator;
use Onliner\CommandBusBundle\Exception\InvalidServiceException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

abstract class AbstractServicePass implements CompilerPassInterface
{
    protected function findServices(ContainerBuilder $container, string $tag, string $interface): Generator
    {
        $services = $container->findTaggedServiceIds($tag, true);
        $parameters = $container->getParameterBag();

        foreach ($services as $id => $tags) {
            $class = $parameters->resolveValue($container->getDefinition($id)->getClass());

            if (!$r = $container->getReflectionClass($class)) {
                throw new InvalidArgumentException(
                    sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id)
                );
            }

            if (!$r->implementsInterface($interface)) {
                throw new InvalidServiceException($id, $tag, $interface);
            }

            yield $id => $tags;
        }
    }
}
