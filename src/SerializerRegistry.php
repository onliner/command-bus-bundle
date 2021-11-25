<?php

declare(strict_types=1);

namespace Onliner\CommandBusBundle;

use Onliner\CommandBus\Remote\Serializer;

class SerializerRegistry
{
    private array $serializers = [];

    public function add(string $name, Serializer $serializer): void
    {
        $this->serializers[$name] = $serializer;
    }

    public function get(string $name): Serializer
    {
        return $this->serializers[$name] ?? throw new Exception\UnknownSerializerException($name);
    }
}
