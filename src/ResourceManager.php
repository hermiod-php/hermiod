<?php

declare(strict_types=1);

namespace Hermiod;

use Hermiod\Resource;

/**
 * @template Type of object
 * @template-implements ResourceManagerInterface<Type>
 */
final class ResourceManager implements ResourceManagerInterface
{
    /**
     * @var array<class-string<Type>, TransposerInterface<Type>>
     */
    private array $transposers = [];

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
                    })
                )
            ),
            new Resource\Hydrator\LaminasHydratorFactory(),
            new Resource\Name\CamelCase(),
        );
    }

    public function __construct(
        private Resource\FactoryInterface $resourceFactory,
        private Resource\Hydrator\FactoryInterface $hydratorFactory,
        private Resource\Name\StrategyInterface $namingStrategy,
    ) {}

    /**
     * @param class-string<Type> $class
     *
     * @return TransposerInterface<Type>
     */
    public function getResource(string $class): TransposerInterface
    {
        return $this->transposers[$class] ??= new Transposer(
            $this->resourceFactory,
            $this->hydratorFactory,
            $this->namingStrategy,
            $class,
        );
    }
}
