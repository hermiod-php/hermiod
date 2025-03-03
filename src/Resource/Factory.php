<?php

declare(strict_types=1);

namespace Hermiod\Resource;

final class Factory implements FactoryInterface
{
    public function __construct(
        private Property\FactoryInterface $properties,
    ) {}

    public function createResourceForClass(string $class): ResourceInterface
    {
        return new Resource($class, $this->properties);
    }
}
