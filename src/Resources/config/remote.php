<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Onliner\CommandBus\Dispatcher;
use Onliner\CommandBus\Remote\RemoteExtension;
use Onliner\CommandBus\Remote\Serializer;
use Onliner\CommandBus\Remote\Serializer\NativeSerializer;
use Onliner\CommandBus\Remote\Transport;
use Onliner\CommandBus\Remote\Transport\MemoryTransport;
use Onliner\CommandBusBundle\Command\ProcessCommand;
use Onliner\CommandBusBundle\DependencyInjection\Compiler\SerializerPass;
use Onliner\CommandBusBundle\SerializerRegistry;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->set(SerializerRegistry::class)

        ->set(NativeSerializer::class)
            ->tag(SerializerPass::TAG, ['name' => 'native'])
            ->alias(Serializer::class, NativeSerializer::class)

        ->set(MemoryTransport::class)
            ->alias(Transport::class, MemoryTransport::class)

        ->set(RemoteExtension::class)
            ->args([
                service(Transport::class),
                service(Serializer::class),
            ])
            ->tag('onliner.commandbus.extension')

        ->set(ProcessCommand::class)
            ->args([
                service(Transport::class),
                service(Dispatcher::class),
            ])
            ->tag('console.command')
    ;
};
