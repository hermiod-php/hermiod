<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Attribute\Constraint\ArrayConstraintInterface;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\Exception\InvalidDefaultValueException;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class ArrayProperty implements PropertyInterface, PrimitiveInterface
{
    use Traits\ConstructWithNameAndNullableTrait;
    use Traits\ConvertToSameJsonValue;

    /**
     * @var array<int|string, mixed>|null
     */
    private array|null $default = null;

    private bool $hasDefault = false;

    /**
     * @var ArrayConstraintInterface[]
     */
    private array $constraints = [];

    /**
     * @param string $name
     * @param bool $nullable
     * @param array<int|string, mixed>|null $default
     */
    public static function withDefaultValue(string $name, bool $nullable, array|null $default): self
    {
        $property = new self($name, $nullable);

        $property->setDefaultValue($default);

        return $property;
    }

    /**
     * @param array<int|string, mixed>|null $value
     */
    private function setDefaultValue(array|null $value): PropertyInterface
    {
        if (!$this->isPossibleValue($value)) {
            throw InvalidDefaultValueException::new('array', $value, $this->nullable);
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

    /**
     * @return array<int|string, mixed>|null
     */
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
                    '%s must be an array%s but %s given',
                    $path->__toString(),
                    $this->nullable ? ' or null' : '',
                    \get_debug_type($value),
                )
            );
        }

        if (null === $value) {
            return new Validation\Result();
        }

        /** @var array<int|string, mixed> $value */
        $list = \array_is_list($value);
        $errors = [];

        foreach ($value as $key => $item) {
            $results = $this->checkElementAgainstConstraints(
                $list ? $path->withArrayKey((int) $key) : $path->withObjectKey((string) $key),
                $item,
            );

            $errors = \array_merge($errors, $results);
        }

        return new Validation\Result(...$errors);
    }

    /**
     * @return array<mixed, mixed>
     */
    public function normalisePhpValue(mixed $value): ?array
    {
        if (\is_array($value)) {
            return $value;
        }

        if (\is_object($value)) {
            return \get_object_vars($value);
        }

        return $this->hasDefaultValue() ? $this->getDefaultValue() : [];
    }

    /**
     * @return list<string>
     */
    private function checkElementAgainstConstraints(PathInterface $path, mixed $value): array
    {
        $errors = [];

        foreach ($this->constraints as $constraint) {
            if (!$constraint->mapValueMatchesConstraint($value)) {
                $errors[] = $constraint->getMismatchExplanation($path, $value);
            }
        }

        return $errors;
    }

    private function isPossibleValue(mixed $value): bool
    {
        return \is_array($value) || ($this->nullable && null === $value);
    }
}
