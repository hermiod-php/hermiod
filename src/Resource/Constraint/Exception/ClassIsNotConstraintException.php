<?php

declare(strict_types=1);

namespace Hermiod\Resource\Constraint\Exception;

use Hermiod\Attribute\Constraint\ConstraintInterface;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
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
