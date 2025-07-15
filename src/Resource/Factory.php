<?php

declare(strict_types=1);

namespace Hermiod\Resource;

use Hermiod\Attribute\ResourceInterface as OptionsInterface;
use Hermiod\Attribute\Resource as Options;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class Factory implements FactoryInterface
{
    /**
     * @var array<class-string, OptionsInterface>
     */
    private static array $options = [];

    public function __construct(
        private readonly Property\FactoryInterface $properties,
        private Name\StrategyInterface $naming,
    ) {}

    public function getPropertyFactory(): Property\FactoryInterface
    {
        return $this->properties;
    }

    /**
     * @inheritdoc
     */
    public function createResourceForClass(string $class): ResourceInterface & PropertyBagInterface
    {
        return new Resource(
            $class,
            $this->properties,
            $this->naming,
            $this->getOptionsForResourceClass($class),
        );
    }

    public function withNamingStrategy(Name\StrategyInterface $strategy): FactoryInterface
    {
        $copy = clone $this;

        $copy->naming = $strategy;

        return $copy;
    }

    /**
     * @template Type of object
     *
     * @param class-string<Type> $class
     *
     * @return OptionsInterface
     *
     * @throws \ReflectionException
     */
    private function getOptionsForResourceClass(string $class): OptionsInterface
    {
        if (isset(self::$options[$class])) {
            return self::$options[$class];
        }

        $reflection = new \ReflectionClass($class);

        $options = \current(
            $reflection->getAttributes(
                OptionsInterface::class,
                \ReflectionAttribute::IS_INSTANCEOF,
            )
        );

        return self::$options[$class] = $options instanceof OptionsInterface
            ? $options
            : new Options();
    }
}
