<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector\Property;

use JsonObjectify\Resource\Attribute\Constraint\ArrayConstraintInterface;
use JsonObjectify\Resource\Path\PathInterface;

final class ArrayProperty implements PropertyInterface
{
    use Traits\ConstructWithNameAndNullableTrait;

    private array|null $default;

    private bool $hasDefault = false;

    /**
     * @var ArrayConstraintInterface[]
     */
    private array $constraints = [];

    public static function withDefaultValue(string $name, bool $nullable, array|null $default): self
    {
        $property = new self($name, $nullable);

        $property->setDefaultValue($default);

        return $property;
    }

    private function setDefaultValue(array|null $value): PropertyInterface
    {
        if (!$this->isPossibleValue($value)) {
            throw new \Exception();
        }

        $this->default = $value;
        $this->hasDefault = true;

        return $this;
    }

    public function withConstraint(ArrayConstraintInterface $constraint): self
    {
        $copy = clone $this;

        $copy->constraints[] = $constraint;

        return $copy;
    }

    public function getDefaultValue(): array|null
    {
        return $this->default;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefault;
    }

    public function checkValueAgainstConstraints(PathInterface $path, mixed $value): Validation\ResultInterface
    {
        if (!$this->isPossibleValue($value)) {
            return new Validation\Result(
                \sprintf(
                    '%s must be an array but %s given',
                    $path->__toString(),
                    \gettype($value)
                )
            );
        }

        foreach ($this->constraints as $constraint) {
            if (!$constraint->valueMatchesConstraint($value)) {
                return new Validation\Result(
                    $constraint->getMismatchExplanation($path, $value),
                );
            }
        }

        return new Validation\Result();
    }

    private function isPossibleValue(mixed $value): bool
    {
        return \is_array($value) || ($this->nullable && null === $value);
    }
}
