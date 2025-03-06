<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
interface FactoryInterface
{
    public function createPropertyFromReflectionProperty(\ReflectionProperty $property): PropertyInterface;
}