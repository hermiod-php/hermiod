<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector\Property;

interface FactoryInterface
{
    public function createPropertyFromReflectionProperty(\ReflectionProperty $property): ?PropertyInterface;
}