<?php

declare(strict_types=1);

namespace Hermiod\Resource\Hydrator;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
interface HydratorInterface
{
    /**
     * @template Type of object
     *
     * @param class-string<Type> $class
     * @param array<mixed>|object $data
     *
     * @return Type & object
     */
    public function hydrate(string $class, array|object $data): object;
}