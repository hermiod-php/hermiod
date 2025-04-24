<?php

declare(strict_types=1);

namespace Hermiod;

use Hermiod\Resource\Unserializer;
use Hermiod\Resource\UnserializerInterface;

/**
 * @template Type of object
 * @template-implements ResourceManagerInterface<Type>
 */
final class ResourceManager implements ResourceManagerInterface
{
    /**
     * @var array<class-string<Type>, UnserializerInterface<Type>>
     */
    private array $unserializers = [];

    /**
     * @return self<Type>
     */
    public static function create(): self
    {
        /** @var self<Type> */
        return new self(
            $factory = new Resource\Factory(
                new Resource\Property\Factory(
                    new Resource\Constraint\CachedFactory(),
                    new Resource\ProxyCallbackFactory(function () use (&$factory) {
                        return $factory;
                    }),
                ),
            ),
            new Resource\Hydrator\LaminasHydratorFactory(),
        );
    }

    public function __construct(
        private Resource\FactoryInterface $resourceFactory,
        private Resource\Hydrator\FactoryInterface $hydratorFactory,
    ) {}

    /**
     * @param class-string<Type> $class
     *
     * @return UnserializerInterface<Type>
     */
    public function getResource(string $class): UnserializerInterface
    {
        return $this->unserializers[$class] ??= new Unserializer(
            $this->resourceFactory,
            $this->hydratorFactory,
            $class,
        );
    }
}
