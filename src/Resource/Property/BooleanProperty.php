<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\Exception\InvalidDefaultValueException;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class BooleanProperty implements PropertyInterface, PrimitiveInterface
{
    use Traits\ConstructWithNameAndNullableTrait;
    use Traits\ConvertToSameJsonValue;

    private bool|null $default = null;

    private bool $hasDefault = false;

    public static function withDefaultValue(string $name, bool $nullable, bool|null $default): self
    {
        $property = new self($name, $nullable);

        $property->setDefaultValue($default);

        return $property;
    }

    private function setDefaultValue(bool|null $value): PropertyInterface
    {
        if (!$this->isPossibleValue($value)) {
            throw InvalidDefaultValueException::new('bool', $value, $this->nullable);
        }

        $this->default = $value;
        $this->hasDefault = true;

        return $this;
    }

    public function getDefaultValue(): bool|null
    {
        return $this->default;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefault;
    }

    public function checkValueAgainstConstraints(PathInterface $path, mixed $value): Validation\ResultInterface
    {
        if ($this->isPossibleValue($value)) {
            return new Validation\Result();
        }

        return new Validation\Result(
            \sprintf(
                '%s must be a boolean but %s given',
                $path->__toString(),
                \get_debug_type($value),
            )
        );
    }

    public function normalisePhpValue(mixed $value): bool|null
    {
        if (\is_bool($value) || (null === $value && $this->nullable)) {
            return $value;
        }

        if (\is_string($value)) {
            return $value === 'true' || $value === '1' || $value === '1.0';
        }

        if (\is_numeric($value)) {
            return $value === 1 || $value === 1.0;
        }

        return $this->hasDefaultValue() ? $this->getDefaultValue() : false;
    }

    private function isPossibleValue(mixed $value): bool
    {
        return \is_bool($value) || ($this->nullable && null === $value);
    }
}
