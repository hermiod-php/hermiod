<?php

declare(strict_types=1);

namespace Hermiod\Resource\Unserializer;

use Hermiod\Resource;

interface FactoryInterface
{
    /**
     * @template Type of object
     *
     * @param class-string<Type> $class
     *
     * @return UnserializerInterface<Type>
     */
    public function createUnserializerForClass(string $class): UnserializerInterface;

    public function withResourceFactory(Resource\FactoryInterface $factory): self;
}
