<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector;

final class Factory implements FactoryInterface
{
    public function __construct(
        private Property\FactoryInterface $properties,
    ) {}

    public function createReflectorForClass(string $class): ReflectorInterface
    {
        return new Reflector($class, $this->properties);
    }
}
