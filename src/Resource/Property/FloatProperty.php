<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Attribute\Constraint\NumberConstraintInterface;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\Exception\InvalidDefaultValueException;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class FloatProperty implements PropertyInterface, PrimitiveInterface
{
    use Traits\ConstructWithNameAndNullableTrait;
    use Traits\ConvertToSameJsonValue;

    private float|null $default = null;

    private bool $hasDefault = false;

    /**
     * @var \Hermiod\Attribute\Constraint\NumberConstraintInterface[]
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
            throw InvalidDefaultValueException::new('float', $value, $this->nullable, 'int');
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
                    \get_debug_type($value),
                )
            );
        }

        if ($value === null) {
            return new Validation\Result();
        }

        foreach ($this->constraints as $constraint) {
            /** @var float|int $value */
            if (!$constraint->valueMatchesConstraint($value)) {
                return new Validation\Result(
                    $constraint->getMismatchExplanation($path, $value),
                );
            }
        }

        return new Validation\Result();
    }

    public function normalisePhpValue(mixed $value): float|null
    {
        if (\is_numeric($value) || \is_bool($value)) {
            return (float) $value;
        }

        if (null === $value && $this->nullable) {
            return null;
        }

        return $this->hasDefaultValue() ? $this->getDefaultValue() : 0;
    }

    private function isPossibleValue(mixed $value): bool
    {
        return \is_float($value) || \is_int($value) || ($this->nullable && null === $value);
    }
}
