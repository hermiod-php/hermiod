<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Exception;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class PropertyClassTypeNotFoundException extends \InvalidArgumentException implements Exception
{
    public static function forTypedClassProperty(string $property, string $type): self
    {
        return new self(
            \sprintf(
                "Unable to locate the class '%s' used in property '%s'",
                $type,
                $property,
            )
        );
    }
}
