<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Attribute\Constraint\StringConstraintInterface;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\Exception\InvalidDefaultValueException;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class StringProperty implements PropertyInterface
{
    use Traits\ConstructWithNameAndNullableTrait;
    use Traits\ConvertToSameJsonValue;

    private string|null $default = null;

    private bool $hasDefault = false;

    /**
     * @var \Hermiod\Attribute\Constraint\StringConstraintInterface[]
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
            throw InvalidDefaultValueException::new('string', $value, $this->nullable);
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
                    \strtolower(\gettype($value)),
                )
            );
        }

        if ($value === null) {
            return new Validation\Result();
        }

        foreach ($this->constraints as $constraint) {
            /** @var string $value */
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

    public function normalisePhpValue(mixed $value): string|null
    {
        if (\is_string($value)) {
            return $value;
        }

        if (null === $value && $this->nullable) {
            return null;
        }

        if (\is_numeric($value)) {
            return (string) $value;
        }

        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return $this->hasDefaultValue() ? $this->getDefaultValue() : '';
    }

    private function isPossibleValue(mixed $value): bool
    {
        return \is_string($value) || ($this->nullable && null === $value);
    }
}
