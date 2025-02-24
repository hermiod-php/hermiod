<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Constraint;

use Hermiod\Attribute\Constraint\ConstraintInterface;

/**
 * @template TClass of ConstraintInterface
 */
final class CachedFactory implements FactoryInterface
{
    /**
     * @var array<string, ConstraintInterface>
     */
    private array $cache = [];

    /**
     * @param class-string<TClass> $class
     * @param array<mixed, mixed> $arguments
     *
     * @return ConstraintInterface
     */
    public function createConstraint(string $class, array $arguments = []): ConstraintInterface
    {
        $key = \sprintf(
            '%s|%s',
            $class,
            \serialize($arguments)
        );

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        if (!\class_exists($class)) {
            throw new \Exception("Class $class does not exist.");
        }

        /** @phpstan-ignore function.alreadyNarrowedType */
        if (!\is_subclass_of($class, ConstraintInterface::class)) {
            throw new \Exception("Class $class must implement ConstraintInterface.");
        }

        $constraint = new $class(...$arguments);

        $this->cache[$key] = $constraint;

        return $constraint;
    }
}
