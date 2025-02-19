<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Resource\Path\PathInterface;

trait MapValueIsInteger
{
    public function mapValueMatchesConstraint(mixed $value): bool
    {
        return \is_int($value);
    }

    public function getMismatchExplanation(PathInterface $path, mixed $value): string
    {
        return \sprintf(
            '%s must be an integer but %s given',
            $path->__toString(),
            \gettype($value)
        );
    }
}