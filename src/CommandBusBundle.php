<?php

declare(strict_types=1);

namespace Onliner\CommandBusBundle;

use Onliner\CommandBusBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CommandBusBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new Compiler\SerializerPass());
        $container->addCompilerPass(new Compiler\MiddlewarePass());
        $container->addCompilerPass(new Compiler\ExtensionPass());
    }
}
