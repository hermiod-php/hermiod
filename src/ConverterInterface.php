<?php

declare(strict_types=1);

namespace Hermiod;

use Hermiod\Exception\ConversionException;
use Hermiod\Result\ResultInterface;

/**
 * @template Type of object
 */
interface ConverterInterface
{
    /**
     * @param class-string<Type> $class
     * @param string|object|array<mixed, mixed> $json
     *
     * @return Type & object
     *
     * @throws ConversionException
     */
    public function toClass(string $class, array|object|string $json): object;

    /**
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
}
