<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property;

use Hermiod\Attribute\Constraint\ObjectConstraintInterface;
use Hermiod\Attribute\Constraint\ObjectKeyConstraintInterface;
use Hermiod\Resource\Path\PathInterface;

final class ObjectProperty implements PropertyInterface
{
    use Traits\ConstructWithNameAndNullableTrait;
    use Traits\ConvertToSamePhpValueOrDefault;

    private object|null $default;

    private bool $hasDefault = false;

    /** @var ObjectConstraintInterface[] */
    private array $valueConstraints = [];

    /** @var ObjectKeyConstraintInterface[] */
    private array $keyConstraints = [];

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
                    '%s must be an object%s but %s given',
                    $path->__toString(),
                    $this->nullable ? ' or null' : '',
                    \gettype($value)
                )
            );
        }

        if ($value === null) {
            return new Validation\Result();
        }

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
    }

    private function isPossibleValue(mixed $value): bool
    {
        return \is_object($value) || (\is_array($value)) && !\array_is_list($value) || ($this->nullable && null === $value);
    }
}
