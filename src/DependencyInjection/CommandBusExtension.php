<?php

declare(strict_types=1);

namespace Onliner\CommandBusBundle\DependencyInjection;

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Remote\RemoteExtension;
use Onliner\CommandBus\Remote\Transport;
use Onliner\CommandBus\Retry\RetryExtension;
use Onliner\CommandBusBundle\Command\ProcessCommand;
use Onliner\CommandBusBundle\TransportFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class CommandBusExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $config = $this->processConfiguration(new Configuration(), $configs);

        $this->loadHandlers($config['handlers'], $container);
        $this->loadRemote($config['remote'], $container, $loader);
        $this->loadRetries($config['retries'], $container, $loader);
    }

    private function loadHandlers(array $config, ContainerBuilder $container): void
    {
        $builder = $container->getDefinition(Builder::class);

        foreach ($config as $command => $handler) {
            $builder->addMethodCall('handle', [$command, new Reference($handler)]);
        }
    }

    private function loadRemote(array $config, ContainerBuilder $container, LoaderInterface $loader): void
    {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('remote.php');

        $extension = $container->getDefinition(RemoteExtension::class);
        $extension->addMethodCall('local', $config['local']);

        $command = $container->getDefinition(ProcessCommand::class);
        $command->addArgument($config['consumer']);

        $transports = [];

        foreach ($config['transport']['connections'] as $name => $connection) {
            $id = sprintf('onliner.commandbus.transport.connection.%s', $name);
            $definition = (new Definition(Transport::class))
                ->setPublic(false)
                ->setFactory([TransportFactory::class, 'create'])
                ->setArguments([$connection['dsn'], $connection['options']]);

            $container->setDefinition($id, $definition);

            $transports[$name] = $id;
        }

        if (empty($transports)) {
            return;
        }

        if (count($transports) == 1) {
            $container->setAlias(Transport::class, current($transports));

            return;
        }

        $default = $config['transport']['default'] ?? array_key_first($transports);

        $definition = (new Definition(Transport\MultiTransport::class))
            ->setPublic(false)
            ->setArguments([new Reference($transports[$default])]);

        foreach ($config['transport']['routes'] ?? [] as $pattern => $key) {
            $definition->addMethodCall('add', [$pattern, new Reference($transports[$key])]);
        }

        $container->setDefinition(Transport\MultiTransport::class, $definition);
        $container->setAlias(Transport::class, Transport\MultiTransport::class);
    }

    private function loadRetries(array $config, ContainerBuilder $container, LoaderInterface $loader): void
    {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('retries.php');

        $retries = $container->getDefinition(RetryExtension::class);

        if ($default = $config['default']) {
            $retries->addArgument(new Reference($default));
        }

        foreach ($config['policies'] as $command => $policy) {
            $retries->addMethodCall('policy', [$command, new Reference($policy)]);
        }
    }
}
