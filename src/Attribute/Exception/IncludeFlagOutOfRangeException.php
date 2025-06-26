<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Exception;

use Hermiod\Attribute\Resource;

final class IncludeFlagOutOfRangeException extends \RangeException implements Exception
{
    public static function forSuppliedValue(Resource $attribute, int $value): self
    {
        $reflection = new \ReflectionClass($attribute);
        $class = \get_class($attribute);

        $constants = \array_filter(
            $reflection->getReflectionConstants(),
            static fn(\ReflectionClassConstant $constant): bool => \str_starts_with($constant->getName(), 'INCLUDE_'),
        );

        $constants = \array_map(
            static fn(\ReflectionClassConstant $constant): string => "{$class}::{$constant->getName()}",
            $constants,
        );

        return new self(
            \sprintf(
                "Invalid value (%s) for property include flags. Available constants: %s",
                $value,
                \implode(', ', $constants),
            ),
        );
    }
}
