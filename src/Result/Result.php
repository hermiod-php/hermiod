<?php

declare(strict_types=1);

namespace Hermiod\Result;

use Hermiod\Resource\Hydrator\HydratorInterface;
use Hermiod\Resource\Path\Root;
use Hermiod\Resource\Property;
use Hermiod\Resource\ResourceInterface;

/**
 * @template Type of object
 *
 * @implements ResultInterface<Type>
 */
final class Result implements ResultInterface
{
    private Property\Validation\ResultInterface $validation;

    /**
     * @var \WeakReference<Type>
     */
    private \WeakReference $instance;

    /**
     * @param ResourceInterface<Type> $resource
     * @param HydratorInterface $hydrator
     * @param object|array<mixed> $json
     */
    public function __construct(
        readonly private ResourceInterface $resource,
        readonly private HydratorInterface $hydrator,
        private object|array &$json,
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
     * @return Type|object
     */
    public function getInstance(): ?object
    {
        if (isset($this->instance) && ($instance = $this->instance->get())) {
            return $instance;
        }

        $result = $this->getValidationResult();

        if (!$result->isValid()) {
            return null;
        }

        $result->hydrate($this->hydrator);

        /** @var Type $instance */
        $instance = $this->hydrator->hydrate(
            $this->resource->getClassName(),
            $this->json,
        );

        $this->instance = \WeakReference::create($instance);

        return $instance;
    }

    private function getValidationResult(): Property\Validation\ResultInterface
    {
        return $this->validation ??= $this->resource->validateAndTranspose(
            new Root(),
            $this->json,
        );
    }
}
