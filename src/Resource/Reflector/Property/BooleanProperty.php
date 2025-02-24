<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Reflector\Property\Exception\InvalidDefaultValueException;

final class BooleanProperty implements PropertyInterface
{
    use Traits\ConstructWithNameAndNullableTrait;
    use Traits\ConvertToSamePhpValueOrDefault;

    private bool|null $default;

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
                \gettype($value)
            )
        );
    }

    private function isPossibleValue(mixed $value): bool
    {
        return \is_bool($value) || ($this->nullable && null === $value);
    }
}
