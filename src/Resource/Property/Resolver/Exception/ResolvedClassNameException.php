<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Resolver\Exception;

final class ResolvedClassNameException extends \RuntimeException implements Exception
{
    /**
     * @param list<string> $possible
     */
    public static function noResolverFor(string $interface, array $possible): self
    {
        return new self(
            \sprintf(
                "No resolver has been mapped for %s. Mapped interfaces [%s]",
                $interface,
                \implode(', ', $possible),
            )
        );
    }

    public static function didNotResolveToString(string $interface, mixed $value): self
    {
        return new self(
            \sprintf(
                "No resolver for %s did not resolve to a class string. %s returned.",
                $interface,
                \get_debug_type($value),
            )
        );
    }

    public static function classIsNotAnImplementationOf(string $interface, string $class): self
    {
        return new self(
            \sprintf(
                "Resolved class %s is not an implementation of the interface %s",
                $class,
                $interface,
            )
        );
    }
}
