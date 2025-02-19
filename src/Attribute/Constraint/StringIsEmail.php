<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Resource\Path\PathInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class StringIsEmail implements StringConstraintInterface
{
    public function valueMatchesConstraint(string $value): bool
    {
        return false !== \filter_var($value, \FILTER_VALIDATE_EMAIL);
    }

    public function getMismatchExplanation(PathInterface $path, string $value): string
    {
        return \sprintf(
            "%s must be an email address but '%s' given",
            $path->__toString(),
            $value,
        );
    }
}
