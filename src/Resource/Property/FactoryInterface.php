<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Resource\Property\Resolver\ResolverInterface;
use Hermiod\Resource\PropertyBagInterface;
use Hermiod\Resource\ResourceInterface;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
interface FactoryInterface
{
    public function createPropertyFromReflectionProperty(\ReflectionProperty $property): PropertyInterface;

    /**
     * @template Type of object
     *
     * @param class-string<Type> $class
     *
     * @return PropertyInterface & ResourceInterface<Type> & PropertyBagInterface
     */
    public function createClassProperty(
        string $name,
        string $class,
        bool $nullable,
        bool $default,
    ): PropertyInterface & ResourceInterface & PropertyBagInterface;

    /**
     * @template Type of object
     *
     * @param class-string<Type> $interface
     * @param array<mixed, mixed> $fragment
     *
     * @return PropertyInterface & ResourceInterface<Type> & PropertyBagInterface
     */
    public function createClassPropertyForInterfaceGivenFragment(
        string $name,
        string $interface,
        bool $nullable,
        bool $default,
        array $fragment,
    ): PropertyInterface & ResourceInterface & PropertyBagInterface;

    public function getInterfaceResolver(): ResolverInterface;
}