<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Attribute\Constraint;

use JsonObjectify\Resource\Path\PathInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ArrayIsList implements ArrayConstraintInterface
{
    public function valueMatchesConstraint(array $value): bool
    {
        return \array_is_list($value);
    }

    public function getMismatchExplanation(PathInterface $path, mixed $value): string
    {
        return \sprintf(
            '%s must be an array as a list but %s given',
            $path->__toString(),
            \gettype($value)
        );
    }
}
