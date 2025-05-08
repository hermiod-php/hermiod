<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Validation;

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

        $copy->validationErrors = \array_merge($this->validationErrors, $error);

        return $copy;
    }

    public function withMergedResult(ResultInterface $result): ResultInterface
    {
        $copy = clone $this;

        $copy->validationErrors = \array_merge(
            $copy->validationErrors,
            $result->getValidationErrors(),
        );

        return $copy;
    }
}
