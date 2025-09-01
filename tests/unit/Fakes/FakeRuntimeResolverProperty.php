<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Fakes;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\Validation\ResultInterface;
use Hermiod\Resource\ResourceInterface;
use Hermiod\Resource\RuntimeResolverInterface;

class FakeRuntimeResolverProperty implements PropertyInterface, RuntimeResolverInterface
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

    public function getConcreteResource(array $fragment): ResourceInterface
    {
        return new FakeResourceProperty();
    }

    public function normaliseJsonValue(mixed $value): mixed
    {
        return $value;
    }
}

