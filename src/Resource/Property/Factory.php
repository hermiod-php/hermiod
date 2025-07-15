<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Attribute\Constraint\ArrayConstraintInterface;
use Hermiod\Attribute\Constraint\ConstraintInterface;
use Hermiod\Attribute\Constraint\NumberConstraintInterface;
use Hermiod\Attribute\Constraint\ObjectKeyConstraintInterface;
use Hermiod\Attribute\Constraint\ObjectValueConstraintInterface;
use Hermiod\Attribute\Constraint\StringConstraintInterface;
use Hermiod\Resource\Property\Exception\UnsupportedPropertyTypeException;
use Hermiod\Resource\Property\Resolver\ResolverInterface;
use Hermiod\Resource\PropertyBagInterface;
use Hermiod\Resource\ResourceInterface;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final readonly class Factory implements FactoryInterface
{
    public function __construct(
        private \Hermiod\Resource\Constraint\FactoryInterface $constraints,
        private \Hermiod\Resource\FactoryInterface $resources,
        private ResolverInterface $resolver,
    ) {}

    public function getInterfaceResolver(): ResolverInterface
    {
        return $this->resolver;
    }

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
                : $this->createUserlandTypePropertyFromReflection($type, $property);
        }

        return $this->createMixedTypeProperty($property);
    }

    /**
     * @inheritdoc
     */
    public function createClassPropertyForInterfaceGivenFragment(
        string $name,
        string $interface,
        bool $nullable,
        bool $default,
        array $fragment,
    ): PropertyInterface & ResourceInterface & PropertyBagInterface
    {
        $class = $this->resolver->resolve($interface, $fragment);

        if ($default) {
            /** @phpstan-ignore return.type */
            return ClassProperty::withDefaultNullValue($name, $class, $nullable, $this->resources);
        }

        /** @phpstan-ignore return.type */
        return new ClassProperty($name, $class, $nullable, $this->resources);
    }

    /**
     * @inheritdoc
     */
    public function createClassProperty(
        string $name,
        string $class,
        bool $nullable,
        bool $default,
    ): PropertyInterface & ResourceInterface & PropertyBagInterface
    {
        if ($default) {
            /** @phpstan-ignore return.type */
            return ClassProperty::withDefaultNullValue($name, $class, $nullable, $this->resources);
        }

        return new ClassProperty($name, $class, $nullable, $this->resources);
    }

    /**
     * @param class-string $interface
     */
    private function createInterfaceProperty(
        string $name,
        string $interface,
        bool $nullable,
        bool $default,
    ): PropertyInterface
    {
        if ($default) {
            return InterfaceProperty::withDefaultNullValue($name, $interface, $nullable, $this);
        }

        return new InterfaceProperty($name, $interface, $nullable, $this);
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
            ? ArrayProperty::withDefaultValue($name, $type->allowsNull(), \is_array($default) ? $default : null)
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
            ? BooleanProperty::withDefaultValue($name, $type->allowsNull(), \is_bool($default) ? $default : null)
            : new BooleanProperty($name, $type->allowsNull());

        return $property;
    }

    private function createIntegerTypeProperty(\ReflectionNamedType $type, \ReflectionProperty $reflection): PropertyInterface
    {
        $name = $reflection->getName();
        $default = $reflection->getDefaultValue();

        $property = $reflection->hasDefaultValue()
            ? IntegerProperty::withDefaultValue($name, $type->allowsNull(), \is_int($default) ? $default : null)
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
            ? FloatProperty::withDefaultValue($name, $type->allowsNull(), \is_float($default) || \is_int($default) ? $default : null)
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
            ? StringProperty::withDefaultValue($name, $type->allowsNull(), \is_string($default) ? $default : null)
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

    private function createUserlandTypePropertyFromReflection(\ReflectionNamedType $type, \ReflectionProperty $reflection): PropertyInterface
    {
        /** @var class-string $class */
        $class = $type->getName();

        if (\is_a($class, \DateTimeInterface::class, true)) {
            return $this->createDateTimeInterfaceProperty($type, $reflection);
        }

        if ($class === \stdClass::class) {
            return $this->createObjectTypeProperty($type, $reflection);
        }

        $name = $reflection->getName();
        $nullable = $type->allowsNull();

        if (\interface_exists($class)) {
            return $this->createInterfaceProperty($name, $class, $nullable, $reflection->hasDefaultValue());
        }

        return $this->createClassProperty($name, $class, $nullable, $reflection->hasDefaultValue());
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
