<?php

declare(strict_types=1);

namespace Hermiod\Resource;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 *
 * @template Type of object
 */
interface RuntimeResolverInterface
{
    /**
     * @param array<mixed, mixed> $fragment
     *
     * @return ResourceInterface<Type>
     */
    public function getConcreteResource(array $fragment): ResourceInterface;
}
