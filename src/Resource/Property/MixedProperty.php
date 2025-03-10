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
    use Traits\ConstructWithNameAndNullableTrait;
    use Traits\ConvertToSamePhpValueOrDefault;

    private mixed $default;

    private bool $hasDefault = false;

    public static function withDefaultValue(string $name, bool $nullable, mixed $default): self
    {
        $property = new self($name, $nullable);

        $property->setDefaultValue($default);

        return $property;
    }

    private function setDefaultValue(mixed $value): PropertyInterface
    {
        $this->default = $value;
        $this->hasDefault = true;

        return $this;
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
}
