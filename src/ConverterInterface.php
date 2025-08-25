<?php

declare(strict_types=1);

namespace Hermiod;

use Hermiod\Exception\ConversionException;
use Hermiod\Result\ResultInterface;

interface ConverterInterface
{
    /**
     * @template Type of object
     *
     * @param class-string<Type> $class
     * @param string|object|array<mixed, mixed> $json
     *
     * @return Type & object
     *
     * @throws ConversionException
     */
    public function toClass(string $class, array|object|string $json): object;

    /**
     * @template Type of object
     *
     * @param class-string<Type> $class
     * @param string|object|array<mixed, mixed> $json
     *
     * @return ResultInterface<Type>
     */
    public function tryToClass(string $class, array|object|string $json): ResultInterface;

    /**
     * @param object $class
     *
     * @return object|null
     */
    public function toJson(object $class): ?object;

    /**
     * @template Type of object
     *
     * @param class-string<Type> $interface
     * @param class-string | callable(array<mixed, mixed> $fragment): class-string $resolver
     */
    public function addInterfaceResolver(string $interface, string|callable $resolver): ConverterInterface;

    public function useNamingStrategy(Resource\Name\StrategyInterface $strategy): ConverterInterface;
}
