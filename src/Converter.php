<?php

declare(strict_types=1);

namespace Hermiod;

use Hermiod\Exception\ConversionException;
use Hermiod\Resource\Unserializer;
use Hermiod\Resource\UnserializerInterface;
use Hermiod\Result\ResultInterface;

/**
 * @template Type of object
 * @template-implements ConverterInterface<Type>
 */
final class Converter implements ConverterInterface
{
    /**
     * @var array<class-string<Type>, UnserializerInterface<Type>>
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
