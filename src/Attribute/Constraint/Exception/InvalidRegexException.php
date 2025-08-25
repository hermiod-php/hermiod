<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Exception;

final class InvalidRegexException extends \InvalidArgumentException implements Exception
{
    public static function invalidRegex(string $regex, string $error): self
    {
        return new self(
            \sprintf(
                "The regex '%s' is invalid due to: %s",
                $regex,
                \strlen($error) === 0 ? 'unknown' : $error,
            )
        );
    }
}
