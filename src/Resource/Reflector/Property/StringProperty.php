<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector\Property;

use JsonObjectify\Resource\Attribute\Constraint\StringConstraintInterface;
use JsonObjectify\Resource\Path\PathInterface;

final class StringProperty implements PropertyInterface
{
    use Traits\ConstructWithNameAndNullableTrait;

    private string|null $default;

    private bool $hasDefault = false;

    /**
     * @var StringConstraintInterface[]
     */
    private array $constraints = [];

    public static function withDefaultValue(string $name, bool $nullable, string|null $default): self
    {
        $property = new self($name, $nullable);

        $property->setDefaultValue($default);

        return $property;
    }

    private function setDefaultValue(string|null $value): PropertyInterface
    {
        if (!$this->isPossibleValue($value)) {
            throw new \Exception();
        }

        $this->default = $value;
        $this->hasDefault = true;

        return $this;
    }

    public function getDefaultValue(): string|null
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
                    '%s must be a string but %s given',
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

    public function withConstraint(StringConstraintInterface $constraint): self
    {
        $copy = clone $this;

        $copy->constraints[] = $constraint;

        return $copy;
    }

    private function isPossibleValue(mixed $value): bool
    {
        return \is_string($value) || ($this->nullable && null === $value);
    }
}
