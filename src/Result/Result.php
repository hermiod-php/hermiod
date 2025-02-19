<?php

declare(strict_types=1);

namespace Hermiod\Result;

use Hermiod\Resource\Hydrator\HydratorInterface;
use Hermiod\Resource\Reflector\ReflectorInterface;
use Hermiod\Resource\Reflector\Property;

/**
 * @template TClass of object
 *
 * @implements ResultInterface<TClass>
 */
final readonly class Result implements ResultInterface
{
    /**
     * @param ReflectorInterface $reflector
     * @param HydratorInterface $hydrator
     * @param object|array<mixed> $json
     */
    public function __construct(
        private ReflectorInterface $reflector,
        private HydratorInterface $hydrator,
        private object|array $json,
    ) {}

    public function isValid(): bool
    {
        return $this->validate()->isValid();
    }

    public function getErrors(): Error\CollectionInterface
    {
        return Error\Collection::fromPropertyValidationResult(
            $this->validate()
        );
    }

    /**
     * @return TClass|object
     *
     * @throws \Exception
     */
    public function instance(): object
    {
        if ($this->isValid()) {
            throw new \Exception();
        }

        return $this->hydrator->hydrate($this->json);
    }

    private function validate(): Property\Validation\ResultInterface
    {
        return $this->reflector->validate($this->json);
    }
}
