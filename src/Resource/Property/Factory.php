<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Attribute\Constraint\ArrayConstraintInterface;
use Hermiod\Attribute\Constraint\ConstraintInterface;
use Hermiod\Attribute\Constraint\NumberConstraintInterface;
use Hermiod\Attribute\Constraint\ObjectValueConstraintInterface;
use Hermiod\Attribute\Constraint\ObjectKeyConstraintInterface;
use Hermiod\Attribute\Constraint\StringConstraintInterface;
use Hermiod\Resource\Property\Exception\UnsupportedPropertyTypeException;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final readonly class Factory implements FactoryInterface
{
    public function __construct(
        private \Hermiod\Resource\Constraint\FactoryInterface $constraints,
        private \Hermiod\Resource\FactoryInterface $resources,
    ) {}

    public function createPropertyFromReflectionProperty(\ReflectionProperty $property): PropertyInterface
    {
        $type = $property->getType();

        if ($type instanceof \ReflectionIntersectionType) {
            return $this->createIntersectionProperty($property);
        }

        if ($type instanceof \ReflectionUnionType) {
            return $this->createUnionProperty($property);
        }

        if ($type instanceof \ReflectionNamedType) {
            return $type->isBuiltin()
                ? $this->createBuiltinProperty($type, $property)
                : $this->createClassProperty($type, $property);
        }

        return $this->createMixedTypeProperty($property);
    }

    private function createBuiltInProperty(\ReflectionNamedType $type, \ReflectionProperty $reflection): PropertyInterface
    {
        return match ($type->getName()) {
            'array' => $this->createArrayTypeProperty($type, $reflection),
            'bool' => $this->createBooleanTypeProperty($type, $reflection),
            'float' => $this->createFloatTypeProperty($type, $reflection),
            'int' => $this->createIntegerTypeProperty($type, $reflection),
            'object' => $this->createObjectTypeProperty($type, $reflection),
            'string' => $this->createStringTypeProperty($type, $reflection),
            'mixed' => $this->createMixedTypeProperty($reflection),
            default => throw UnsupportedPropertyTypeException::noFactoryFor($type->getName()),
        };
    }

    private function createIntersectionProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        return new StringProperty('Foo', false);
    }

    private function createUnionProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        return new StringProperty('Foo', false);
    }

    private function createArrayTypeProperty(\ReflectionNamedType $type, \ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $default = $reflection->getDefaultValue();

        $property = $reflection->hasDefaultValue()
            ? ArrayProperty::withDefaultValue($name, $type->allowsNull(), \is_array($default) ? $default : [])
            : new ArrayProperty($name, $type->allowsNull());

        foreach ($this->loadConstraintAttributes($reflection, ArrayConstraintInterface::class) as $constraint) {
            $property = $property->withConstraint($constraint);
        }

        return $property;
    }

    private function createObjectTypeProperty(\ReflectionNamedType $type, \ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();

        $property = new ObjectProperty($name, $type->allowsNull());

        foreach ($this->loadConstraintAttributes($reflection, ObjectKeyConstraintInterface::class) as $constraint) {
            $property = $property->withKeyConstraint($constraint);
        }

        foreach ($this->loadConstraintAttributes($reflection, ObjectValueConstraintInterface::class) as $constraint) {
            $property = $property->withValueConstraint($constraint);
        }

        return $property;
    }

    private function createBooleanTypeProperty(\ReflectionNamedType $type, \ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $default = $reflection->getDefaultValue();

        $property = $reflection->hasDefaultValue()
            ? BooleanProperty::withDefaultValue($name, $type->allowsNull(), \is_bool($default) ? $default : false)
            : new BooleanProperty($name, $type->allowsNull());

        return $property;
    }

    private function createIntegerTypeProperty(\ReflectionNamedType $type, \ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $default = $reflection->getDefaultValue();

        $property = $reflection->hasDefaultValue()
            ? IntegerProperty::withDefaultValue($name, $type->allowsNull(), \is_int($default) ? $default : 0)
            : new IntegerProperty($name, $type->allowsNull());

        foreach ($this->loadConstraintAttributes($reflection, NumberConstraintInterface::class) as $constraint) {
            $property = $property->withConstraint($constraint);
        }

        return $property;
    }

    private function createFloatTypeProperty(\ReflectionNamedType $type, \ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $default = $reflection->getDefaultValue();

        $property = $reflection->hasDefaultValue()
            ? FloatProperty::withDefaultValue($name, $type->allowsNull(), \is_float($default) || \is_int($default) ? $default : 0.0)
            : new FloatProperty($name, $type->allowsNull());

        foreach ($this->loadConstraintAttributes($reflection, NumberConstraintInterface::class) as $constraint) {
            $property = $property->withConstraint($constraint);
        }

        return $property;
    }

    private function createStringTypeProperty(\ReflectionNamedType $type, \ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $default = $reflection->getDefaultValue();

        $property = $reflection->hasDefaultValue()
            ? StringProperty::withDefaultValue($name, $type->allowsNull(), \is_string($default) ? $default : '')
            : new StringProperty($name, $type->allowsNull());

        foreach ($this->loadConstraintAttributes($reflection, StringConstraintInterface::class) as $constraint) {
            $property = $property->withConstraint($constraint);
        }

        return $property;
    }

    private function createMixedTypeProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();

        if ($reflection->hasDefaultValue()) {
            return MixedProperty::withDefaultValue($name, $reflection->getDefaultValue());
        }

        return new MixedProperty($name);
    }

    /**
     * TODO: Add support for other date extensions like Laravel Carbon and Symfony DatePoint
     */
    private function createDateTimeInterfaceProperty(\ReflectionNamedType $type, \ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();

        if ($reflection->hasDefaultValue()) {
            return DateTimeInterfaceProperty::withDefaultNullValue($name);
        }

        return new DateTimeInterfaceProperty($name, $type->allowsNull());
    }

    private function createClassProperty(\ReflectionNamedType $type, \ReflectionProperty $reflection): PropertyInterface
    {
        /** @var class-string $class */
        $class = $type->getName();

        if (\is_a($class, \DateTimeInterface::class, true)) {
            return $this->createDateTimeInterfaceProperty($type, $reflection);
        }

        $name = $reflection->getName();
        $nullable = $type->allowsNull();

        if ($reflection->hasDefaultValue()) {
            return ClassProperty::withDefaultNullValue($name, $class, $nullable, $this->resources);
        }

        return new ClassProperty($name, $class, $nullable, $this->resources);
    }

    /**
     * We are collecting these in a hashmap to deduplicate erroneous usage.
     *
     * @template TAttribute of ConstraintInterface
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
            $attributes[] = $this->constraints->createConstraint(
                $attribute->getName(),
                $attribute->getArguments(),
            );
        }

        /** @var TAttribute[] $attributes */
        return $attributes;
    }
}
