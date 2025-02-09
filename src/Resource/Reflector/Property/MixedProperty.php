<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector\Property;

use JsonObjectify\Resource\Path\PathInterface;

final class MixedProperty implements PropertyInterface
{
    use Traits\ConstructWithNameAndNullableTrait;

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
