<?php

declare(strict_types=1);

namespace JsonObjectify;

use JsonObjectify\Resource\Reflector;
use JsonObjectify\Resource\Hydrator;
use JsonObjectify\Result\Result;
use JsonObjectify\Result\ResultInterface;

final class Objectifier
{
    public function __construct(
        private ?Reflector\FactoryInterface $reflections = null,
        private ?Hydrator\FactoryInterface $hydrators = null,
    )
    {
        $this->reflections ??= new Reflector\Factory(
            new Resource\Reflector\Property\Factory()
        );

        $this->hydrators ??= new Hydrator\LaminasReflectionHydratorFactory();
    }

    /**
     * @template T
     *
     * @param string|object|array $json
     * @param class-string<T> $targetClass
     *
     * @return ResultInterface<T>
     */
    public function decode(string|object|array $json, string $targetClass): ResultInterface
    {
        $reflector = $this->reflections->createReflectorForClass($targetClass);
        $hydrator = $this->hydrators->createHydratorForClass($targetClass);

        if (\is_string($json)) {
            $json = \json_decode($json, false, flags: JSON_THROW_ON_ERROR);
        }

        return new Result(
            $reflector,
            $hydrator,
            $json
        );
    }
}
