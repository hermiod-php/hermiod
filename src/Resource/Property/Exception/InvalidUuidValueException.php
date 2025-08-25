<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Exception;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class InvalidUuidValueException extends \InvalidArgumentException implements Exception
{
    public static function new(string $uuid, ?\Throwable $previous = null): self
    {
        return new self(
            \sprintf(
                "Unable to parse UUID value from '%s'",
                $uuid,
            ),
            previous: $previous,
        );
    }
}
