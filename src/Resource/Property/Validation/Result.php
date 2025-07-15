<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Validation;

use Hermiod\Resource\Hydrator\HydratorInterface;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class Result implements ResultInterface
{
    /**
     * @var list<string>
     */
    private array $validationErrors = [];

    /**
     * @var array<int, callable(HydratorInterface $hydrator): void>
     */
    private array $stages = [];

    public function __construct(string ...$validationError)
    {
        $this->validationErrors = $validationError;
    }

    public function isValid(): bool
    {
        return \count($this->validationErrors) === 0;
    }

    /**
     * @return list<string>
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function withErrors(string ...$error): ResultInterface
    {
        $copy = clone $this;

        $copy->validationErrors = \array_merge($copy->validationErrors, $error);

        return $copy;
    }

    public function withHydrationStage(callable $callback): ResultInterface
    {
        $copy = clone $this;

        $copy->stages[] = $callback;

        return $copy;
    }

    public function hydrate(HydratorInterface $hydrator): void
    {
        foreach (\array_reverse($this->stages) as $stage) {
            $stage($hydrator);
        }
    }

    public function merge(ResultInterface $result): ResultInterface
    {
        $copy = clone $this;

        $copy->validationErrors = \array_merge($copy->validationErrors, $result->getValidationErrors());

        $copy->stages[] = function (HydratorInterface $hydrator) use ($result): void {
            $result->hydrate($hydrator);
        };

        return $copy;
    }
}
