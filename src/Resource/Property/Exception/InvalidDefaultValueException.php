<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Exception;

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
                "The value %s is not a valid default for the property type '%s'. Acceptable types are %s.",
                \print_r($given, true),
                $type,
                \implode(', ', $otherAcceptableTypes),
            )
        );
    }
}
