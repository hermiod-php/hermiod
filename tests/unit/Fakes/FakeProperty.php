<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Fakes;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\Validation;

class FakeProperty implements PropertyInterface
{
    public function getPropertyName(): string
    {
        return 'name';
    }

    public function getDefaultValue(): mixed
    {
        return null;
    }

    public function hasDefaultValue(): bool
    {
        return false;
    }

    public function isNullable(): bool
    {
        return false;
    }

    public function checkValueAgainstConstraints(PathInterface $path, mixed $value): Validation\ResultInterface
    {
        return new Validation\Result();
    }

    public function normaliseJsonValue(mixed $value): mixed
    {
        return $value;
    }
}
