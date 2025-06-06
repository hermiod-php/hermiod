<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Exception;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class InvalidPropertyNameException extends \DomainException implements Exception
{
    public static function new(string $name): self
    {
        return new self(
            \sprintf(
                "The property name '%s' is not valid. Must be a valid PHP property name.",
                $name,
            )
        );
    }
}
