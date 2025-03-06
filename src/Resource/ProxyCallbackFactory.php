<?php

declare(strict_types=1);

namespace Hermiod\Resource;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class ProxyCallbackFactory implements FactoryInterface
{
    private FactoryInterface $factory;

    public function __construct(
        private readonly \Closure $resolver
    ) {}

    public function createResourceForClass(string $class): ResourceInterface
    {
        return $this->getFactory()->createResourceForClass($class);
    }

    private function getFactory(): FactoryInterface
    {
        if (isset($this->factory)) {
            return $this->factory;
        }

        $factory = $this->resolver->__invoke();

        if (!$factory instanceof FactoryInterface) {
            throw new \RuntimeException();
        }

        return $this->factory = $factory;
    }
}
