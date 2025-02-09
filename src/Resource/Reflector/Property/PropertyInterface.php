<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector\Property;

use JsonObjectify\Resource\Path\PathInterface;

interface PropertyInterface
{
    public function getPropertyName(): string;

    public function getDefaultValue(): mixed;

    public function hasDefaultValue(): bool;

    public function checkValueAgainstConstraints(PathInterface $path, mixed $value): Validation\ResultInterface;
}
