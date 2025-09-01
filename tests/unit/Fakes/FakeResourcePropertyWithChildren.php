<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Fakes;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\CollectionInterface;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\Validation\ResultInterface;
use Hermiod\Resource\ResourceInterface;

class FakeResourcePropertyWithChildren implements PropertyInterface, ResourceInterface
{
    public function getPropertyName(): string
    {
        return 'testProperty';
    }

    public function hasDefaultValue(): bool
    {
        return false;
    }

    public function getDefaultValue(): mixed
    {
        return null;
    }

    public function isNullable(): bool
    {
        return false;
    }

    public function checkValueAgainstConstraints(PathInterface $path, mixed $value): ResultInterface
    {
        return new FakeValidationResult();
    }

    public function getClassName(): string
    {
        return 'NestedClass';
    }

    public function getProperties(): CollectionInterface
    {
        // Return a collection with actual properties to trigger recursion
        return new FakePropertyCollection();
    }

    public function validateAndTranspose(PathInterface $path, object|array &$json): ResultInterface
    {
        return new FakeValidationResult();
    }

    public function canAutomaticallySerialise(): bool
    {
        return true;
    }

    public function normaliseJsonValue(mixed $value): mixed
    {
        return $value;
    }
}

