<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Resolver;

interface ResolverInterface
{
    /**
     * @param class-string $interface
     * @param class-string | callable(array<mixed, mixed>): class-string $resolver
     *
     * @return void
     *
     * @throws Exception\Exception
     */
    public function addResolver(string $interface, string|callable $resolver): void;

    /**
     * @param class-string $interface
     * @param array<mixed, mixed> $fragment
     *
     * @return class-string
     *
     * @throws Exception\Exception
     */
    public function resolve(string $interface, array $fragment): string;
}
