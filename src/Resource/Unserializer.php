<?php

declare(strict_types=1);

namespace Hermiod\Resource;

use Hermiod\Exception\JsonValueMustBeObjectException;
use Hermiod\Resource;
use Hermiod\Result\Result;
use Hermiod\Result\ResultInterface;

/**
 * @template Type of object
 * @template-implements UnserializerInterface<Type>
 */
final readonly class Unserializer implements UnserializerInterface
{
    /**
     * @param Resource\FactoryInterface $reflections
     * @param class-string<Type> $class
     */
    public function __construct(
        private Resource\FactoryInterface $reflections,
        private Hydrator\FactoryInterface $hydrators,
        private string $class,
    ) {}

    /**
     * @param string|object|array<mixed, mixed> $json
     *
     * @return ResultInterface<Type>
     */
    public function unserialize(string|object|array $json): ResultInterface
    {
        $resource = $this->reflections->createResourceForClass($this->class);
        $hydrator = $this->hydrators->createHydrator();

        if (\is_string($json)) {
            $json = \json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        }

        if (\is_object($json) || (\is_array($json) && !\array_is_list($json))) {
            return new Result(
                $resource,
                $hydrator,
                $json,
            );
        }

        throw JsonValueMustBeObjectException::new($json);
    }
}
