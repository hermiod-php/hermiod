<?php

declare(strict_types=1);

namespace Hermiod\Resource\Hydrator;

use Laminas\Hydrator\ReflectionHydrator;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 *
 * @template Type of object
 */
final class LaminasHydratorFactory implements FactoryInterface
{
    /**
     * @var HydratorInterface<Type>[]
     */
    private array $hydrators = [];

    /**
     * @param class-string<Type> $class
     *
     * @return HydratorInterface<Type>
     */
    public function createHydratorForClass(string $class): HydratorInterface
    {
        return $this->hydrators[$class] ??= new LaminasHydrator(
            $class,
            new ReflectionHydrator()
        );
    }
}
