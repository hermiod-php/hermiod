<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Resource\Path\PathInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class NumberGreaterThanOrEqual implements NumberConstraintInterface
{
    public function __construct(
        private int|float $value
    ) {}

    public function valueMatchesConstraint(int|float $value): bool
    {
        return $value >= $this->value;
    }

    public function getMismatchExplanation(PathInterface $path, int|float $value): string
    {
        return \sprintf(
            '%s must be a number greater than or equal to %d but %d given',
            $path->__toString(),
            $this->value,
            $value
        );
    }
}
