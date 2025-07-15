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

    public function createResourceForClass(string $class): ResourceInterface & PropertyBagInterface
    {
        return $this->getFactory()->createResourceForClass($class);
    }

    public function getPropertyFactory(): Property\FactoryInterface
    {
        return $this->getFactory()->getPropertyFactory();
    }

    public function withNamingStrategy(Name\StrategyInterface $strategy): FactoryInterface
    {
        return new self(
            fn () => $this->getFactory()->withNamingStrategy($strategy),
        );
    }

    private function getFactory(): FactoryInterface
    {
        /**
         * We want this to \TypeError if the callback returns an incorrect type
         *
         * @phpstan-ignore return.type
         */
        return $this->resolver->__invoke();
    }
}
