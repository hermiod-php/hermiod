<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Resolver;

final class Resolver implements ResolverInterface
{
    /**
     * @var array<class-string, class-string | callable(array<mixed, mixed>): class-string>
     */
    private array $resolvers = [];

    /**
     * @param class-string $interface
     * @param class-string | callable(array<mixed, mixed>): class-string $resolver
     *
     * @return void
     */
    public function addResolver(string $interface, callable|string $resolver): void
    {
        if (!\interface_exists($interface)) {
            throw Exception\InterfaceNotFoundException::for($interface);
        }

        if (\is_callable($resolver)) {
            $this->resolvers[$interface] = $resolver;

            return;
        }

        if (!\class_exists($resolver)) {
            throw Exception\ResolvedClassNameException::noSuchClass($resolver);
        }

        if (!\is_a($resolver, $interface, true)) {
            throw Exception\ResolvedClassNameException::classIsNotAnImplementationOf($interface, $resolver);
        }

        $this->resolvers[$interface] = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function resolve(string $interface, array $fragment): string
    {
        if (!\interface_exists($interface)) {
            throw Exception\InterfaceNotFoundException::for($interface);
        }

        return $this->getClass($interface, $fragment);
    }

    /**
     * @param class-string $interface
     * @param array<mixed, mixed> $fragment
     *
     * @return class-string
     */
    private function getClass(string $interface, array $fragment): string
    {
        if (!isset($this->resolvers[$interface])) {
            throw Exception\ResolvedClassNameException::noResolverFor($interface, \array_keys($this->resolvers));
        }

        $resolved = $this->resolvers[$interface];

        if (!\is_callable($resolved)) {
            /** @var class-string $resolved */
            return $resolved;
        }

        $resolved = $resolved($fragment);

        if (!\is_string($resolved)) {
            throw Exception\ResolvedClassNameException::didNotResolveToString($interface, $resolved);
        }

        if (!\is_a($resolved, $interface, true)) {
            throw Exception\ResolvedClassNameException::classIsNotAnImplementationOf($interface, $resolved);
        }

        return $resolved;
    }
}
