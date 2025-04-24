<?php

declare(strict_types=1);

namespace Hermiod\Resource;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
interface FactoryInterface
{
    /**
     * @template Type of object
     *
     * @param class-string<Type> $class
     *
     * @return ResourceInterface<Type>
     */
    public function createResourceForClass(string $class): ResourceInterface;
}