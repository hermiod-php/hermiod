<?php

declare(strict_types=1);

namespace Hermiod\Resource\Unserializer;

use Hermiod\Exception\JsonValueMustBeObjectException;
use Hermiod\Resource;
use Hermiod\Resource\Hydrator;
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
     *
     * @throws \Hermiod\Exception\Exception
     */
    public function unserialize(string|object|array $json): ResultInterface
    {
        $resource = $this->reflections->createResourceForClass($this->class);
        $hydrator = $this->hydrators->createHydrator();

        if (\is_string($json)) {
            $json = \json_decode($json, true);
        }

        if (null === $json && \JSON_ERROR_NONE !== \json_last_error()) {
            throw JsonValueMustBeObjectException::invalidJson(\json_last_error_msg());
        }

        if (\is_object($json)) {
            return new Result(
                $resource,
                $hydrator,
                $json,
            );
        }

        if (!\is_array($json)) {
            throw JsonValueMustBeObjectException::invalidType($json);
        }

        if (\count($json) === 0 || !\array_is_list($json)) {
            return new Result(
                $resource,
                $hydrator,
                $json,
            );
        }

        throw JsonValueMustBeObjectException::invalidType($json);
    }
}
