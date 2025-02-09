<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector\Property;

use JsonObjectify\Resource\Attribute\Constraint\NumberConstraintInterface;
use JsonObjectify\Resource\Path\PathInterface;

final class FloatProperty implements PropertyInterface
{
    use Traits\ConstructWithNameAndNullableTrait;

    private float|null $default;

    private bool $hasDefault = false;

    /**
     * @var NumberConstraintInterface[]
     */
    private array $constraints = [];

    public static function withDefaultValue(string $name, bool $nullable, int|float|null $default): self
    {
        $property = new self($name, $nullable);

        $property->setDefaultValue($default);

        return $property;
    }

    private function setDefaultValue(int|float|null $value): PropertyInterface
    {
        if (!$this->isPossibleValue($value)) {
            throw new \Exception();
        }

        $this->default = $value;
        $this->hasDefault = true;

        return $this;
    }

    public function withConstraint(NumberConstraintInterface $constraint): self
    {
        $copy = clone $this;

        $copy->constraints[] = $constraint;

        return $copy;
    }

    public function getDefaultValue(): float|null
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
                    '%s must be a float but %s given',
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
        return \is_float($value) || \is_int($value) || ($this->nullable && null === $value);
    }
}
