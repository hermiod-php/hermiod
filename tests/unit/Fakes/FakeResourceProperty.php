<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Fakes;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\CollectionInterface;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\Validation\ResultInterface;
use Hermiod\Resource\ResourceInterface;

class FakeResourceProperty implements PropertyInterface, ResourceInterface
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
        // Return an empty collection to avoid infinite recursion
        return new class implements CollectionInterface {
            public function getIterator(): \Iterator
            {
                return new \ArrayIterator([]);
            }

            public function offsetGet(mixed $offset): ?PropertyInterface
            {
                return null;
            }

            public function current(): ?PropertyInterface
            {
                return null;
            }

            public function next(): void {}

            public function key(): mixed
            {
                return null;
            }

            public function valid(): bool
            {
                return false;
            }

            public function rewind(): void {}

            public function offsetExists(mixed $offset): bool
            {
                return false;
            }

            public function offsetSet(mixed $offset, mixed $value): void {}

            public function offsetUnset(mixed $offset): void {}
        };
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
