<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector\Property;

use JsonObjectify\Resource\Path\PathInterface;

final class ObjectProperty implements PropertyInterface
{
    use Traits\ConstructWithNameAndNullableTrait;

    private object|null $default;

    private bool $hasDefault = false;

    public static function withDefaultValue(string $name, bool $nullable, object|null $default): self
    {
        $property = new self($name, $nullable);

        $property->setDefaultValue($default);

        return $property;
    }

    private function setDefaultValue(object|null $value): PropertyInterface
    {
        if (!$this->isPossibleValue($value)) {
            throw new \Exception();
        }

        $this->default = $value;
        $this->hasDefault = true;

        return $this;
    }

    public function getDefaultValue(): object|null
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
                '%s must be an object but %s given',
                $path->__toString(),
                \gettype($value)
            )
        );
    }

    private function isPossibleValue(mixed $value): bool
    {
        return \is_object($value) || (\is_array($value)) && !\array_is_list($value) || ($this->nullable && null === $value);
    }
}
