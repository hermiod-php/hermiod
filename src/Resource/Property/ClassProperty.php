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
 * @template-implements Resource\ResourceInterface<Type>
 */
final class ClassProperty implements PropertyInterface, Resource\ResourceInterface
{
    use Traits\GetPropertyNameTrait;

    /**
     * @var Resource\ResourceInterface<Type> & Resource\PropertyBagInterface
     */
    private Resource\ResourceInterface $resource;

    private bool $hasDefault = false;

    /**
     * @param class-string<Type> $class
     *
     * @return self<Type>
     */
    public static function withDefaultNullValue(
        string $name,
        string $class,
        bool $nullable,
        Resource\FactoryInterface $factory,
    ): self
    {
        $property = new self($name, $class, $nullable, $factory);

        $property->hasDefault = true;

        return $property;
    }

    /**
     * @param class-string<Type> $class
     */
    public function __construct(
        string $name,
        private readonly string $class,
        private readonly bool $nullable,
        private readonly Resource\FactoryInterface $factory,
    )
    {
        $this->setName($name);

        if (!\class_exists($this->class)) {
            throw PropertyClassTypeNotFoundException::forTypedClassProperty(
                $this->name,
                $this->class,
            );
        }
    }

    public function getClassName(): string
    {
        return $this->class;
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

        if (!$value instanceof $this->class) {
            return null;
        }

        if ($value instanceof \JsonSerializable) {
            return $value->jsonSerialize();
        }

        if (!$this->canAutomaticallySerialise()) {
            return null;
        }

        $encoded = [];
        $reflection = new \ReflectionClass($value);

        foreach ($this->getInnerResource()->getProperties() as $property) {
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
            return $this->validateAndTranspose($path, $value);
        }

        return new Validation\Result(
            'Must be iterable'
        );
    }

    public function validateAndTranspose(PathInterface $path, object|array &$json): Validation\ResultInterface
    {
        return $this->getInnerResource()->validateAndTranspose($path, $json);
    }

    public function canAutomaticallySerialise(): bool
    {
        return $this->getInnerResource()->canAutomaticallySerialise();
    }

    public function getProperties(): CollectionInterface
    {
        return $this->getInnerResource()->getProperties();
    }

    /**
     * @return Resource\ResourceInterface<Type> & Resource\PropertyBagInterface
     */
    private function getInnerResource(): Resource\ResourceInterface & Resource\PropertyBagInterface
    {
        return $this->resource ??= $this->factory->createResourceForClass($this->class);
    }
}
