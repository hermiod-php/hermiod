<?php

declare(strict_types=1);

namespace Hermiod\Resource\Constraint;

use Hermiod\Attribute\Constraint\ConstraintInterface;

interface FactoryInterface
{
    /**
     * @template TClass of ConstraintInterface
     *
     * @param class-string<TClass> $class
     * @param array<mixed, mixed> $arguments
     *
     * @return ConstraintInterface
     */
    public function createConstraint(string $class, array $arguments = []): ConstraintInterface;
}