<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Exception;

use Hermiod\Resource\Property\CollectionInterface;

final class AddingToSealedCollectionException extends \InvalidArgumentException implements Exception
{
    public static function new(CollectionInterface $collection, mixed $offset, mixed $value): self
    {
        return new self(
            \sprintf(
                'Failed to add %s to %s[%s], The collection is sealed and new properties cannot be added',
                \is_object($value) ? \get_class($value) : \gettype($value),
                \get_class($collection),
                \is_string($offset) || \is_numeric($offset) ? (string)$offset : \gettype($offset),
            )
        );
    }
}
