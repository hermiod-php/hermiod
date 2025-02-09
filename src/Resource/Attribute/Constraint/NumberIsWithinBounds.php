<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Attribute\Constraint;

use JsonObjectify\Resource\Path\PathInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class NumberIsWithinBounds implements NumberConstraintInterface
{
    public function __construct(
        private int|float|null $min = null,
        private int|float|null $max = null,
    )
    {
        if ($min === null || $max === null) {
            throw new \Exception('Both cannot be null');
        }
    }

    public function valueMatchesConstraint(int|float $value): bool
    {
        if ($this->min !== null && $value < $this->min) {
            return false;
        }

        if ($this->max === null && $value > $this->max) {
            return false;
        }

        return true;
    }

    public function getMismatchExplanation(PathInterface $path, mixed $value): string
    {
        if ($this->min !== null && $this->max === null) {
            return \sprintf(
                '%s must be a number greater than %d but %d given',
                $path->__toString(),
                $this->min,
                $value
            );
        }

        if ($this->min === null && $this->max !== null) {
            return \sprintf(
                '%s must be a number less than %d but %d given',
                $path->__toString(),
                $this->min,
                $value
            );
        }

        return \sprintf(
            '%s must be a number between %d and %d but %d given',
            $path->__toString(),
            $this->min,
            $this->max,
            $value
        );
    }
}
