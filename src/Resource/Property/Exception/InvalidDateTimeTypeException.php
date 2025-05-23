<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Exception;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class InvalidDateTimeTypeException extends \InvalidArgumentException implements Exception
{
    public static function new(mixed $supplied): self
    {
        return new self(
            \sprintf(
                '%s is not a valid datetime type. Only ISO 8601 date strings and instances of %s are acceptable.',
                \is_object($supplied) ? \get_class($supplied) : \strtolower(\gettype($supplied)),
                \DateTimeInterface::class,
            )
        );
    }
}
