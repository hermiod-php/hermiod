<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector\Property;

use JsonObjectify\Resource\Attribute\Constraint\ArrayConstraintInterface;
use JsonObjectify\Resource\Attribute\Constraint\NumberConstraintInterface;
use JsonObjectify\Resource\Attribute\Constraint\StringConstraintInterface;
use JsonObjectify\Resource\Reflector\Property\Constraint\LogicalOr;

final class Factory implements FactoryInterface
{
    public function createPropertyFromReflectionProperty(\ReflectionProperty $property): ?PropertyInterface
    {
        $type = $property->getType();

        if (null === $type) {
            return $this->createMixedTypeProperty($property);
        }

        if ($type instanceof \ReflectionIntersectionType) {
            return $this->createIntersectionProperty($property);
        }

        if ($type instanceof \ReflectionUnionType) {
            return $this->createUnionProperty($property);
        }

        if ($type instanceof \ReflectionNamedType && $type->isBuiltin()) {
            return match ($type->getName()) {
                'array' => $this->createArrayTypeProperty($property),
                'bool' => $this->createBooleanTypeProperty($property),
                'float' => $this->createFloatTypeProperty($property),
                'int' => $this->createIntegerTypeProperty($property),
                'object' => $this->createObjectTypeProperty($property),
                'string' => $this->createStringTypeProperty($property),
                'mixed' => $this->createMixedTypeProperty($property),
                default => null,
            };
        }

        return $this->createMixedTypeProperty($property);
    }

    private function createIntersectionProperty(\ReflectionProperty $reflection): PropertyInterface
    {

    }

    private function createUnionProperty(\ReflectionProperty $reflection): PropertyInterface
    {

    }

    private function createArrayTypeProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $nullable = $reflection->getType()->allowsNull();

        $property = $reflection->hasDefaultValue()
            ? ArrayProperty::withDefaultValue($name, $nullable, $reflection->getDefaultValue())
            : new ArrayProperty($name, $nullable);

        foreach ($this->loadConstraintAttributes($reflection, ArrayConstraintInterface::class) as $constraint) {
            $property = $property->withConstraint($constraint);
        }

        return $property;
    }

    private function createObjectTypeProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $nullable = $reflection->getType()->allowsNull();

        $property = $reflection->hasDefaultValue()
            ? ObjectProperty::withDefaultValue($name, $nullable, $reflection->getDefaultValue())
            : new ObjectProperty($name, $nullable);

        return $property;
    }

    private function createBooleanTypeProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $nullable = $reflection->getType()->allowsNull();

        $property = $reflection->hasDefaultValue()
            ? BooleanProperty::withDefaultValue($name, $nullable, $reflection->getDefaultValue())
            : new BooleanProperty($name, $nullable);

        return $property;
    }

    private function createIntegerTypeProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $nullable = $reflection->getType()->allowsNull();

        $property = $reflection->hasDefaultValue()
            ? IntegerProperty::withDefaultValue($name, $nullable, $reflection->getDefaultValue())
            : new IntegerProperty($name, $nullable);

        foreach ($this->loadConstraintAttributes($reflection, NumberConstraintInterface::class) as $constraint) {
            $property = $property->withConstraint($constraint);
        }

        return $property;
    }

    private function createFloatTypeProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $nullable = $reflection->getType()->allowsNull();

        $property = $reflection->hasDefaultValue()
            ? FloatProperty::withDefaultValue($name, $nullable, $reflection->getDefaultValue())
            : new FloatProperty($name, $nullable);

        foreach ($this->loadConstraintAttributes($reflection, NumberConstraintInterface::class) as $constraint) {
            $property = $property->withConstraint($constraint);
        }

        return $property;
    }

    private function createStringTypeProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $nullable = $reflection->getType()->allowsNull();

        $property = $reflection->hasDefaultValue()
            ? StringProperty::withDefaultValue($name, $nullable, $reflection->getDefaultValue())
            : new StringProperty($name, $nullable);

        foreach ($this->loadConstraintAttributes($reflection, StringConstraintInterface::class) as $constraint) {
            $property = $property->withConstraint($constraint);
        }

        return $property;
    }

    private function createMixedTypeProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $nullable = $reflection->getType()?->allowsNull() ?? true;

        return $reflection->hasDefaultValue()
            ? MixedProperty::withDefaultValue($name, $nullable, $reflection->getDefaultValue())
            : new MixedProperty($name, $nullable);
    }

    /**
     * @template TAttribute
     *
     * @param \ReflectionProperty $reflection
     * @param class-string<TAttribute> $class
     *
     * @return TAttribute[]
     */
    private function loadConstraintAttributes(\ReflectionProperty $reflection, string $class): array
    {
        $attributes = [];

        foreach ($reflection->getAttributes($class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            /** @var TAttribute $instance */
            $instance = $attribute->newInstance();

            $attributes[\get_class($instance)] = $instance;
        }

        return $attributes;
    }
}
