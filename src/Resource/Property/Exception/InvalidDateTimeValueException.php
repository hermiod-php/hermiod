<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Exception;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class InvalidDateTimeValueException extends \InvalidArgumentException implements Exception
{
    public static function new(string $datetime, ?\Throwable $previous = null): self
    {
        return new self(
            \sprintf(
                "Unable to parse ISO 8601 datetime value from '%s'.%s",
                $datetime,
                $previous ? ' ' . $previous->getMessage() : '',
            ),
            previous: $previous,
        );
    }
}
