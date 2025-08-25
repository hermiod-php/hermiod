<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource;
use Hermiod\Resource\Property\Exception\PropertyClassTypeNotFoundException;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 *
 * @template Type of object
 * @template-implements Resource\RuntimeResolverInterface<Type>
 */
final class InterfaceProperty implements PropertyInterface, Resource\RuntimeResolverInterface
{
    use Traits\GetPropertyNameTrait;

    private bool $hasDefault = false;

    /**
     * @param class-string<Type> $interface
     *
     * @return self<Type>
     */
    public static function withDefaultNullValue(
        string $name,
        string $interface,
        bool $nullable,
        Resource\Property\FactoryInterface $factory,
    ): self
    {
        $property = new self($name, $interface, $nullable, $factory);

        $property->hasDefault = true;

        return $property;
    }

    /**
     * @param class-string<Type> $interface
     */
    public function __construct(
        string $name,
        private readonly string $interface,
        private readonly bool $nullable,
        private readonly Resource\Property\FactoryInterface $factory,
    )
    {
        $this->setName($name);

        if (!\interface_exists($this->interface)) {
            throw PropertyClassTypeNotFoundException::forTypedClassProperty(
                $this->name,
                $this->interface,
            );
        }
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function getDefaultValue(): null
    {
        return null;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefault;
    }

    public function normaliseJsonValue(mixed $value): mixed
    {
        if (!\is_object($value)) {
            return null;
        }

        if (!$value instanceof $this->interface) {
            return null;
        }

        if ($value instanceof \JsonSerializable) {
            return $value->jsonSerialize();
        }

        $concretion = $this->factory->createClassProperty(
            $this->name,
            \get_class($value),
            $this->nullable,
            $this->hasDefault,
        );

        if (!$concretion->canAutomaticallySerialise()) {
            return null;
        }

        $reflection = new \ReflectionClass($value);
        $encoded = [];

        foreach ($concretion->getProperties() as $property) {
            $name = $property->getPropertyName();

            $encoded[$name] = $property->normaliseJsonValue(
                $reflection->getProperty($name)->getValue()
            );
        }

        return (object) $encoded;
    }

    public function checkValueAgainstConstraints(PathInterface $path, mixed $value): Validation\ResultInterface
    {
        if ($value === null && $this->nullable) {
            return new Validation\Result();
        }

        if (\is_array($value) || \is_object($value)) {
            return $this->getConcreteResource((array)$value)->validateAndTranspose($path, $value);
        }

        return new Validation\Result(
            'Must be iterable'
        );
    }

    /**
     * @inheritdoc
     */
    public function getConcreteResource(array $fragment): Resource\ResourceInterface
    {
        return $this->factory->createClassPropertyForInterfaceGivenFragment(
            $this->name,
            $this->interface,
            $this->nullable,
            $this->hasDefault,
            $fragment,
        );
    }
}
