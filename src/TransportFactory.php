<?php

declare(strict_types=1);

namespace Onliner\CommandBusBundle;

use Onliner\CommandBus\Remote\AMQP\AMQPTransport;
use Onliner\CommandBus\Remote\Transport;

class TransportFactory
{
    public const DEFAULT = 'memory://memory';

    public static function default(): Transport
    {
        return self::create(self::DEFAULT);
    }

    public static function create(string $dsn, array $options = []): Transport
    {
        return match (parse_url($dsn, PHP_URL_SCHEME)) {
            'amqp' => AMQPTransport::create($dsn, $options),
            'memory' => new Transport\MemoryTransport(),
            default => throw new Exception\BadTransportException($dsn),
        };
    }
}
