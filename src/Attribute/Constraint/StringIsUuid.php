<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Resource\Path\PathInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class StringIsUuid implements StringConstraintInterface
{
    private const VALID_PATTERN = '\A[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}\z';

    public function valueMatchesConstraint(string $value): bool
    {
        return \is_string($value) || \preg_match('/' . self::VALID_PATTERN . '/Dms', $value);
    }

    public function getMismatchExplanation(PathInterface $path, mixed $value): string
    {
        return \sprintf(
            "%s must be a UUID string but '%s' given",
            $path->__toString(),
            $value,
        );
    }
}
