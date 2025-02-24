<?php

declare(strict_types=1);

namespace Hermiod\Result;

use Hermiod\Resource\Hydrator\HydratorInterface;
use Hermiod\Resource\Reflector\ReflectorInterface;
use Hermiod\Resource\Reflector\Property;
use Hermiod\Result\Exception\InvalidJsonPayloadException;

/**
 * @template TClass of object
 *
 * @implements ResultInterface<TClass>
 */
final class Result implements ResultInterface
{
    private Property\Validation\ResultInterface $validation;

    /**
     * @param ReflectorInterface $reflector
     * @param HydratorInterface $hydrator
     * @param object|array<mixed> $json
     */
    public function __construct(
        readonly private ReflectorInterface $reflector,
        readonly private HydratorInterface $hydrator,
        readonly private object|array $json,
    ) {}

    public function isValid(): bool
    {
        return $this->getValidationResult()->isValid();
    }

    public function getErrors(): Error\CollectionInterface
    {
        return Error\Collection::fromPropertyValidationResult(
            $this->getValidationResult()
        );
    }

    /**
     * @return TClass|object
     *
     * @throws \Exception
     */
    public function instance(): object
    {
        if (!$this->isValid()) {
            throw InvalidJsonPayloadException::new(
                $this->hydrator->getTargetClassname(),
                $this->getValidationResult()->getValidationErrors(),
            );
        }

        return $this->hydrator->hydrate($this->json);
    }

    private function getValidationResult(): Property\Validation\ResultInterface
    {
        return $this->validation ??= $this->reflector->validate($this->json);
    }
}
