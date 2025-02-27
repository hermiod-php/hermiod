<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Resource\Path\PathInterface;

interface PropertyInterface
{
    public function getPropertyName(): string;

    public function getDefaultValue(): mixed;

    public function hasDefaultValue(): bool;

    public function checkValueAgainstConstraints(PathInterface $path, mixed $value): Validation\ResultInterface;

    public function convertToPhpValue(mixed $value): mixed;

    public function convertToJsonValue(mixed $value): mixed;
}
