<?php

declare(strict_types=1);

namespace Hermiod;

use Hermiod\Exception\ConversionException;
use Hermiod\Resource\Unserializer;
use Hermiod\Resource\UnserializerInterface;
use Hermiod\Result\ResultInterface;

/**
 * @template Type of object
 */
final class Converter implements ConverterInterface
{
    /**
     * @var array<class-string, UnserializerInterface<Type>>
     */
    private array $unserializers = [];

    /**
     * @return self<Type>
     */
    public static function create(): self
    {
        /** @var self<Type> */
        return new self(
            $factory = new Resource\Factory(
                new Resource\Property\Factory(
                    new Resource\Constraint\CachedFactory(),
                    new Resource\ProxyCallbackFactory(function () use (&$factory) {
                        return $factory;
                    }),
                    new Resource\Property\Resolver\Resolver(),
                ),
                new Resource\Name\CachedNamingStrategy(
                    new Resource\Name\CamelCase(),
                ),
            ),
            new Resource\Hydrator\LaminasHydratorFactory(),
        );
    }

    public function __construct(
        private Resource\FactoryInterface $resourceFactory,
        private Resource\Hydrator\FactoryInterface $hydratorFactory,
    ) {}

    /**
     * @param class-string<Type> $class
     * @param string|object|array<mixed, mixed> $json
     *
     * @return Type & object
     *
     * @throws ConversionException
     */
    public function toClass(string $class, array|object|string $json): object
    {
        $result = $this->tryToClass($class, $json);
        $object = $result->getInstance();

        if ($object === null) {
            throw ConversionException::dueToTranspositionErrors($result->getErrors());
        }

        return $object;
    }

    /**
     * @param class-string<Type> $class
     * @param string|object|array<mixed, mixed> $json
     *
     * @return ResultInterface<Type>
     */
    public function tryToClass(string $class, array|object|string $json): ResultInterface
    {
        return $this->getUnserializer($class)->unserialize($json);
    }

    /**
     * @param object $class
     *
     * @return object|null
     */
    public function toJson(object $class): ?object
    {
        return null;
    }

    /**
     * @param class-string<Type> $interface
     * @param class-string | callable(array<mixed, mixed> $fragment): class-string $resolver
     */
    public function addInterfaceResolver(string $interface, string|callable $resolver): ConverterInterface
    {
        $this->resourceFactory
            ->getPropertyFactory()
            ->getInterfaceResolver()
            ->addResolver($interface, $resolver);

        return $this;
    }

    public function useNamingStrategy(Resource\Name\StrategyInterface $strategy): ConverterInterface
    {
        $this->resourceFactory = $this->resourceFactory->withNamingStrategy(
            Resource\Name\CachedNamingStrategy::wrap($strategy)
        );

        $this->unserializers = [];

        return $this;
    }

    /**
     * @param class-string<Type> $class
     *
     * @return UnserializerInterface<Type>
     */
    private function getUnserializer(string $class): UnserializerInterface
    {
        return $this->unserializers[$class] ??= new Unserializer(
            $this->resourceFactory,
            $this->hydratorFactory,
            $class,
        );
    }
}
