<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Hydrator;

use Laminas\Hydrator\ReflectionHydrator;

final class LaminasReflectionHydratorFactory implements FactoryInterface
{
    /**
     * @var HydratorInterface[]
     */
    private array $hydrators = [];

    public function createHydratorForClass(string $class): HydratorInterface
    {
        return $this->hydrators[$class] ??= new LaminasHydrator(
            $class,
            new ReflectionHydrator()
        );
    }
}
