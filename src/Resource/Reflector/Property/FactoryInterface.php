<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property;

interface FactoryInterface
{
    public function createPropertyFromReflectionProperty(\ReflectionProperty $property): PropertyInterface;
}