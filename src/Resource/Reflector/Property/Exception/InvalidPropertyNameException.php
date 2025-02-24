<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property\Exception;

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
