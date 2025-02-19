<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property;

use Hermiod\Attribute\Constraint\ArrayConstraintInterface;
use Hermiod\Resource\Path\PathInterface;

final class ArrayProperty implements PropertyInterface
{
    use Traits\ConstructWithNameAndNullableTrait;
    use Traits\ConvertToSamePhpValueOrDefault;

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
                    '%s must be an array%s but %s given',
                    $path->__toString(),
                    $this->nullable ? ' or null' : '',
                    \gettype($value)
                )
            );
        }

        if (null === $value) {
            return new Validation\Result();
        }

        foreach ($value as $key => $item) {
            $result = $this->checkElementAgainstConstraints($path->withArrayKey($key), $item);

            if ($result !== null) {
                return new Validation\Result($result);
            }
        }

        return new Validation\Result();
    }

    private function checkElementAgainstConstraints(PathInterface $path, mixed $value): ?string
    {
        foreach ($this->constraints as $constraint) {
            if (!$constraint->mapValueMatchesConstraint($value)) {
                return $constraint->getMismatchExplanation($path, $value);
            }
        }
    }

    private function isPossibleValue(mixed $value): bool
    {
        return \is_array($value) || ($this->nullable && null === $value);
    }
}
