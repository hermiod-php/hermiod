<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Constraint\Exception;

use Hermiod\Attribute\Constraint\ConstraintInterface;

final class ClassIsNotConstraintException extends \InvalidArgumentException implements Exception
{
    public static function new(string $class): self
    {
        return new self(
            \sprintf(
                'The class %s does not implement %s',
                $class,
                ConstraintInterface::class,
            )
        );
    }
}
