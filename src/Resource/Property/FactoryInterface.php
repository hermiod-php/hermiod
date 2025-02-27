<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

interface FactoryInterface
{
    public function createPropertyFromReflectionProperty(\ReflectionProperty $property): PropertyInterface;
}