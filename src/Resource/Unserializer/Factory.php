<?php

declare(strict_types=1);

namespace Hermiod\Resource\Unserializer;

use Hermiod\Resource;

final class Factory implements FactoryInterface
{
    /**
     * @var array<class-string, UnserializerInterface<object>>
     */
    private array $cache = [];

    public function __construct(
        private Resource\FactoryInterface $resourceFactory,
        private Resource\Hydrator\FactoryInterface $hydratorFactory,
    ) {}

    /**
     * @inheritDoc
     */
    public function createUnserializerForClass(string $class): UnserializerInterface
    {
        return $this->cache[$class] ??= new Unserializer(
            $this->resourceFactory,
            $this->hydratorFactory,
            $class,
        );
    }

    public function withResourceFactory(Resource\FactoryInterface $factory): self
    {
        $copy = clone $this;

        $copy->resourceFactory = $factory;
        $copy->cache = [];

        return $copy;
    }
}
