<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property\Exception;

final class UnsupportedPropertyTypeException extends \InvalidArgumentException implements Exception
{
    public static function new(string $type): self
    {
        return new self(
            \sprintf(
                "No factory is available for the PHP type '%s'",
                $type,
            )
        );
    }
}
