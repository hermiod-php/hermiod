<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Validation;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
interface ResultInterface
{
    public function isValid(): bool;

    /**
     * @return string[]
     */
    public function getValidationErrors(): array;
}
