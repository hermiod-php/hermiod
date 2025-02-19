<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property\Validation;

use Hermiod\Result\Error;

interface ResultInterface
{
    public function isValid(): bool;

    /**
     * @return string[]
     */
    public function getValidationErrors(): array;
}
