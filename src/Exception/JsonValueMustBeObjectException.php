<?php

declare(strict_types=1);

namespace Hermiod\Exception;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class JsonValueMustBeObjectException extends \InvalidArgumentException implements Exception
{
    public static function new(mixed $actual): self
    {
        return new self(
            \sprintf(
                'JSON string must decode to an object, but resulting type was %s.',
                \gettype($actual),
            )
        );
    }
}
