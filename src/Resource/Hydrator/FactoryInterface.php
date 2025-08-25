<?php

declare(strict_types=1);

namespace Hermiod\Resource\Hydrator;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
interface FactoryInterface
{
    /**
     * @return HydratorInterface
     */
    public function createHydrator(): HydratorInterface;
}