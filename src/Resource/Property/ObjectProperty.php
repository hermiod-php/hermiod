<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Attribute\Constraint\ObjectConstraintInterface;
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

    /** @var ObjectConstraintInterface[] */
    private array $valueConstraints = [];

    /**
     * @var ObjectKeyConstraintInterface[]
     *
     * @phpstan-ignore property.onlyWritten
     */
    private array $keyConstraints = [];

    public function withValueConstraint(ObjectConstraintInterface $constraint): self
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

    public function getDefaultValue(): object|null
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
                    '%s must be an object%s but %s given',
                    $path->__toString(),
                    $this->nullable ? ' or null' : '',
                    \strtolower(\gettype($value)),
                )
            );
        }

        if ($value === null) {
            return new Validation\Result();
        }

        /** @var array<string, mixed> $value */
        foreach ($value as $key => $item) {
            $result = $this->checkElementAgainstConstraints($path->withObjectKey($key), $item);

            if ($result !== null) {
                return new Validation\Result($result);
            }
        }

        return new Validation\Result();
    }

    private function checkElementAgainstConstraints(PathInterface $path, mixed $value): ?string
    {
        foreach ($this->valueConstraints as $constraint) {
            if (!$constraint->mapValueMatchesConstraint($value)) {
                return $constraint->getMismatchExplanation($path, $value);
            }
        }

        return null;
    }

    public function normalisePhpValue(mixed $value): mixed
    {
        if (null === $value && $this->hasDefaultValue()) {
            return $this->getDefaultValue();
        }

        return $value;
    }

    private function isPossibleValue(mixed $value): bool
    {
        return \is_object($value) || (\is_array($value)) && !\array_is_list($value) || ($this->nullable && null === $value);
    }
}
