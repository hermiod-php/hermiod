<?php

declare(strict_types=1);

namespace Hermiod\Resource;

use Hermiod\Attribute\ResourceInterface as OptionsInterface;
use Hermiod\Attribute\Resource as Options;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 *
 * @template Type of object
 */
final class Factory implements FactoryInterface
{
    /**
     * @var array<class-string, OptionsInterface>
     */
    private static array $options = [];

    public function __construct(
        private readonly Property\FactoryInterface $properties,
    ) {}

    /**
     * @param class-string<Type> $class
     *
     * @return Resource<Type>
     */
    public function createResourceForClass(string $class): ResourceInterface
    {
        return new Resource(
            $class,
            $this->properties,
            $this->getOptionsForResourceClass($class),
        );
    }

    /**
     * @param class-string<Type> $class
     *
     * @return OptionsInterface
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
