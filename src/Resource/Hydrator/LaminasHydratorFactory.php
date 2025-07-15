<?php

declare(strict_types=1);

namespace Hermiod\Resource\Hydrator;

use Laminas\Hydrator\ReflectionHydrator;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class LaminasHydratorFactory implements FactoryInterface
{
    public function createHydrator(): HydratorInterface
    {
        return new LaminasHydrator(
            new ReflectionHydrator()
        );
    }
}
