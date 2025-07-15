<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Validation;

use Hermiod\Resource\Hydrator\HydratorInterface;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
interface ResultInterface
{
    public function isValid(): bool;

    /**
     * @return list<string>
     */
    public function getValidationErrors(): array;

    public function withErrors(string ...$error): ResultInterface;

    /**
     * @param callable(HydratorInterface $hydrator): void $callback
     */
    public function withHydrationStage(callable $callback): ResultInterface;

    public function hydrate(HydratorInterface $hydrator): void;

    public function merge(ResultInterface $result): ResultInterface;
}
