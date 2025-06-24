<?php

declare(strict_types=1);

namespace Hermiod\Resource;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class ProxyCallbackFactory implements FactoryInterface
{
    public function __construct(
        private readonly \Closure $resolver
    ) {}

    public function createResourceForClass(string $class): ResourceInterface
    {
        return $this->getFactory()->createResourceForClass($class);
    }

    private function getFactory(): FactoryInterface
    {
        /** @phpstan-ignore return.type */
        return $this->resolver->__invoke();
    }
}
