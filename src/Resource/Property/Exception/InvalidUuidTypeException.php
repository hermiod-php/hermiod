<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Exception;

final class InvalidUuidTypeException extends \InvalidArgumentException implements Exception
{
    public static function new(mixed $supplied): self
    {
        return new self(
            \sprintf(
                '%s is not a valid type for use with ramsey/uuid. Expected instance of %s or string.',
                \is_object($supplied) ? \get_class($supplied) : \gettype($supplied),
                \Ramsey\Uuid\UuidInterface::class,
            )
        );
    }
}
