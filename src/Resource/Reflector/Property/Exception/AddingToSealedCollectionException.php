<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property\Exception;

final class AddingToSealedCollectionException extends \InvalidArgumentException implements Exception
{
    public static function new(mixed $offset, mixed $value): self
    {
        return new self(
            \sprintf(
                'Failed to add %s to %s[%s], The collection is sealed and new properties cannot be added',
                \is_object($value) ? \get_class($value) : \gettype($value),
                \Hermiod\Resource\Reflector\Property\Collection::class,
                \is_string($offset) || \is_numeric($offset) ? (string)$offset : \gettype($offset),
            )
        );
    }
}
