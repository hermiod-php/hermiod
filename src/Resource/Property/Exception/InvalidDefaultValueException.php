<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Exception;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class InvalidDefaultValueException extends \InvalidArgumentException implements Exception
{
    public static function new(string $type, mixed $given, bool $nullable, string ...$otherAcceptableTypes): self
    {
        $otherAcceptableTypes[] = $type;

        if ($nullable) {
            $otherAcceptableTypes[] = 'null';
        }

        return new self(
            \sprintf(
                "The value type (%s) is not a valid default for the property type (%s). Acceptable %s (%s).",
                \get_debug_type($given),
                $type,
                \count($otherAcceptableTypes) > 1 ? 'types are' : 'type is',
                \implode('), (', $otherAcceptableTypes),
            )
        );
    }
}
