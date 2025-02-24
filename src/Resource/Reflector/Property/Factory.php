<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property;

use Hermiod\Attribute\Constraint\ArrayConstraintInterface;
use Hermiod\Attribute\Constraint\ConstraintInterface;
use Hermiod\Attribute\Constraint\NumberConstraintInterface;
use Hermiod\Attribute\Constraint\ObjectConstraintInterface;
use Hermiod\Attribute\Constraint\ObjectKeyConstraintInterface;
use Hermiod\Attribute\Constraint\StringConstraintInterface;
use Hermiod\Resource\Reflector\Constraint;

final readonly class Factory implements FactoryInterface
{
    public function __construct(
        private Constraint\FactoryInterface $constraints,
    ) {}

    public function createPropertyFromReflectionProperty(\ReflectionProperty $property): PropertyInterface
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
                default => throw new \Exception(),
            };
        }

        return $this->createMixedTypeProperty($property);
    }

    private function createIntersectionProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        return new StringProperty('Foo', false);
    }

    private function createUnionProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        return new StringProperty('Foo', false);
    }

    private function createArrayTypeProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $nullable = $reflection->getType()?->allowsNull() ?? true;
        $default = $reflection->getDefaultValue();

        $property = $reflection->hasDefaultValue()
            ? ArrayProperty::withDefaultValue($name, $nullable, \is_array($default) ? $default : null)
            : new ArrayProperty($name, $nullable);

        foreach ($this->loadConstraintAttributes($reflection, ArrayConstraintInterface::class) as $constraint) {
            $property = $property->withConstraint($constraint);
        }

        return $property;
    }

    private function createObjectTypeProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $nullable = $reflection->getType()?->allowsNull() ?? true;

        $property = new ObjectProperty($name, $nullable);

        foreach ($this->loadConstraintAttributes($reflection, ObjectKeyConstraintInterface::class) as $constraint) {
            $property = $property->withKeyConstraint($constraint);
        }

        foreach ($this->loadConstraintAttributes($reflection, ObjectConstraintInterface::class) as $constraint) {
            $property = $property->withValueConstraint($constraint);
        }

        return $property;
    }

    private function createBooleanTypeProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $nullable = $reflection->getType()?->allowsNull() ?? true;
        $default = $reflection->getDefaultValue();

        $property = $reflection->hasDefaultValue()
            ? BooleanProperty::withDefaultValue($name, $nullable, \is_bool($default) ? $default : null)
            : new BooleanProperty($name, $nullable);

        return $property;
    }

    private function createIntegerTypeProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $nullable = $reflection->getType()?->allowsNull() ?? true;
        $default = $reflection->getDefaultValue();

        $property = $reflection->hasDefaultValue()
            ? IntegerProperty::withDefaultValue($name, $nullable, \is_int($default) ? $default : null)
            : new IntegerProperty($name, $nullable);

        foreach ($this->loadConstraintAttributes($reflection, NumberConstraintInterface::class) as $constraint) {
            $property = $property->withConstraint($constraint);
        }

        return $property;
    }

    private function createFloatTypeProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $nullable = $reflection->getType()?->allowsNull() ?? true;
        $default = $reflection->getDefaultValue();

        $property = $reflection->hasDefaultValue()
            ? FloatProperty::withDefaultValue($name, $nullable, \is_float($default) || \is_int($default) ? $default : null)
            : new FloatProperty($name, $nullable);

        foreach ($this->loadConstraintAttributes($reflection, NumberConstraintInterface::class) as $constraint) {
            $property = $property->withConstraint($constraint);
        }

        return $property;
    }

    private function createStringTypeProperty(\ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $nullable = $reflection->getType()?->allowsNull() ?? true;
        $default = $reflection->getDefaultValue();

        $property = $reflection->hasDefaultValue()
            ? StringProperty::withDefaultValue($name, $nullable, \is_string($default) ? $default : null)
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
            $name = $attribute->getName();

            if (!isset($attributes[$name])) {
                $attributes[$name] = $this->constraints->createConstraint(
                    $name,
                    $attribute->getArguments(),
                );
            }
        }

        /** @var TAttribute[] $attributes */
        return \array_values($attributes);
    }
}
