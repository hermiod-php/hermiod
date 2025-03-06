<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Exception;

use Hermiod\Resource\Property\CollectionInterface;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class DeletingFromSealedCollectionException extends \InvalidArgumentException implements Exception
{
    public static function new(CollectionInterface $collection, mixed $offset): self
    {
        return new self(
            \sprintf(
                'Failed to remove %s[%s], The collection is sealed and new properties cannot be added',
                \get_class($collection),
                \is_string($offset) || \is_numeric($offset) ? (string)$offset : \gettype($offset),
            )
        );
    }
}
