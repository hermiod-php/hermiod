<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property\Validation;

final class Result implements ResultInterface
{
    /**
     * @var string[]
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

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
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
