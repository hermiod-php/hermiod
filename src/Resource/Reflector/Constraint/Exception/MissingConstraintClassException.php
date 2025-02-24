<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Constraint\Exception;

final class MissingConstraintClassException extends \InvalidArgumentException implements Exception
{
    public static function new(string $class): self
    {
        return new self(
            \sprintf(
                'Unable to load constraint class %s',
                $class,
            )
        );
    }
}
