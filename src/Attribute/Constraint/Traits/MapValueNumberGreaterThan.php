<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Traits\JsonCompatibleTypeName;

trait MapValueNumberGreaterThan
{
    use JsonCompatibleTypeName;

    public function __construct(
        private int|float $value
    ) {}

    public function mapValueMatchesConstraint(mixed $value): bool
    {
        if (\is_int($value) || \is_float($value)) {
            return $value > $this->value;
        }

        return false;
    }

    public function getMismatchExplanation(PathInterface $path, mixed $value): string
    {
        return \sprintf(
            '%s must be a number greater than %s but %s given',
            $path->__toString(),
            $this->value,
            (\is_int($value) || \is_float($value)) ? $value : $this->getTypeName($value),
        );
    }
}