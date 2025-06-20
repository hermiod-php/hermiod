<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Attribute\Constraint\ObjectValueConstraintInterface;
use Hermiod\Attribute\Constraint\ObjectKeyConstraintInterface;
use Hermiod\Resource\Path\PathInterface;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class ObjectProperty implements PropertyInterface
{
    use Traits\ConstructWithNameAndNullableTrait;
    use Traits\ConvertToSameJsonValue;

    /** @var ObjectValueConstraintInterface[] */
    private array $valueConstraints = [];

    /**
     * @var ObjectKeyConstraintInterface[]
     */
    private array $keyConstraints = [];

    public function withValueConstraint(ObjectValueConstraintInterface $constraint): self
    {
        $copy = clone $this;

        $copy->valueConstraints[] = $constraint;

        return $copy;
    }

    public function withKeyConstraint(ObjectKeyConstraintInterface $constraint): self
    {
        $copy = clone $this;

        $copy->keyConstraints[] = $constraint;

        return $copy;
    }

    public function getDefaultValue(): null
    {
        return null;
    }

    public function hasDefaultValue(): bool
    {
        return false;
    }

    public function checkValueAgainstConstraints(PathInterface $path, mixed $value): Validation\ResultInterface
    {
        if (!$this->isPossibleValue($value)) {
            return new Validation\Result(
                \sprintf(
                    '%s must be an object, array%s but %s given',
                    $path->__toString(),
                    $this->nullable ? ' or null' : '',
                    \get_debug_type($value),
                )
            );
        }

        if ($value === null) {
            return new Validation\Result();
        }

        /** @var array<int|string, mixed>|object $value */
        if (\is_object($value)) {
            $value = \get_object_vars($value);
        }

        $errors = [];

        foreach ($value as $key => $item) {
            $key = (string)$key;

            $results = $this->checkElementAgainstConstraints(
                $path,
                $key,
                $item,
            );

            $errors = \array_merge($errors, $results);
        }

        return new Validation\Result(...$errors);
    }

    /**
     * @return list<string>
     */
    private function checkElementAgainstConstraints(PathInterface $path, string $key, mixed $value): array
    {
        $errors = [];

        foreach ($this->keyConstraints as $constraint) {
            if (!$constraint->mapKeyMatchesConstraint($key)) {
                $errors[] = $constraint->getMismatchExplanation($path, $key);
            }
        }

        $path = $path->withObjectKey($key);

        foreach ($this->valueConstraints as $constraint) {
            if (!$constraint->mapValueMatchesConstraint($value)) {
                $errors[] = $constraint->getMismatchExplanation($path, $value);
            }
        }

        return $errors;
    }

    public function normalisePhpValue(mixed $value): object|null
    {
        if (\is_object($value)) {
            return $value instanceof \stdClass
                ? $value
                : (object) \get_object_vars($value);
        }

        if (\is_array($value)) {
            return (object) $value;
        }

        if ($this->nullable && null === $value) {
            return null;
        }

        return new \stdClass();
    }

    private function isPossibleValue(mixed $value): bool
    {
        return \is_object($value) || \is_array($value) || ($this->nullable && null === $value);
    }
}
