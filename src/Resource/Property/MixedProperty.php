<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Resource\Path\PathInterface;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class MixedProperty implements PropertyInterface
{
    use Traits\ConvertToSameJsonValue;

    private mixed $default = null;

    private bool $hasDefault = false;

    public function __construct(
        private readonly string $name
    ) {}

    public static function withDefaultValue(string $name, mixed $default): self
    {
        $property = new self($name);

        $property->default = $default;
        $property->hasDefault = true;

        return $property;
    }

    public function getPropertyName(): string
    {
        return $this->name;
    }

    public function getDefaultValue(): mixed
    {
        return $this->default;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefault;
    }

    public function checkValueAgainstConstraints(PathInterface $path, mixed $value): Validation\ResultInterface
    {
        return new Validation\Result();
    }

    public function normalisePhpValue(mixed $value): mixed
    {
        if (null === $value && $this->hasDefaultValue()) {
            return $this->getDefaultValue();
        }

        return $value;
    }
}
