<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector\Property\Validation;

use JsonObjectify\Result\Error;

interface ResultInterface
{
    public function isValid(): bool;

    /**
     * @return string[]
     */
    public function getValidationErrors(): array;
}
