<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource;
use MongoDB\BSON\Type;

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
     * @var Resource\ResourceInterface<Type>
     */
    private Resource\ResourceInterface $resource;

    private bool $hasDefault = false;

    /**
     * @param class-string<Type> $class
     *
     * @return self<Type>
     */
    public static function withDefaultNullValue(string $name, string $class, bool $nullable, Resource\FactoryInterface $factory): self
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
            throw new \RuntimeException("Class '$class' does not exist");
        }
    }

    public function getDefaultValue(): null
    {
        return null;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefault;
    }

    public function normalisePhpValue(mixed $value): mixed
    {
        return $value;
    }

    public function normaliseJsonValue(mixed $value): mixed
    {
        return $value;
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

    /**
     * @return Resource\ResourceInterface<Type>
     */
    private function getInnerResource(): Resource\ResourceInterface
    {
        return $this->resource ??= $this->factory->createResourceForClass($this->class);
    }

    public function getProperties(): CollectionInterface
    {
        return $this->getInnerResource()->getProperties();
    }

    public function validateAndTranspose(PathInterface $path, object|array &$json): Validation\ResultInterface
    {
        return $this->getInnerResource()->validateAndTranspose($path, $json);
    }
}
