<?php

declare(strict_types=1);

namespace Hermiod\Resource\Hydrator;

use Laminas\Hydrator\ReflectionHydrator;

final class LaminasHydratorFactory implements FactoryInterface
{
    /**
     * @var HydratorInterface[]
     */
    private array $hydrators = [];

    /**
     * @template Type of object
     *
     * @param class-string<Type> $class
     */
    public function createHydratorForClass(string $class): HydratorInterface
    {
        return $this->hydrators[$class] ??= new LaminasHydrator(
            $class,
            new ReflectionHydrator()
        );
    }
}
