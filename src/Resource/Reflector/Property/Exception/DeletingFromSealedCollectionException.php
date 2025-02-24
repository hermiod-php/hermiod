<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property\Exception;

final class DeletingFromSealedCollectionException extends \InvalidArgumentException implements Exception
{
    public static function new(mixed $offset): self
    {
        return new self(
            \sprintf(
                'Failed to remove %s[%s], The collection is sealed and new properties cannot be added',
                \Hermiod\Resource\Reflector\Property\Collection::class,
                \is_string($offset) || \is_numeric($offset) ? (string)$offset : \gettype($offset),
            )
        );
    }
}
