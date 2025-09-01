<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Fakes;

use Hermiod\Resource\Property\CollectionInterface;
use Hermiod\Resource\Property\PropertyInterface;

class FakePropertyCollection implements CollectionInterface
{
    private array $properties = [];

    public function __construct()
    {
        // Add a fake property to avoid empty iteration
        $this->properties = [new FakeProperty()];
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->properties);
    }

    public function offsetGet(mixed $offset): ?PropertyInterface
    {
        return $this->properties[$offset] ?? null;
    }

    public function current(): ?PropertyInterface
    {
        return \current($this->properties) ?: null;
    }

    public function next(): void
    {
        \next($this->properties);
    }

    public function key(): mixed
    {
        return \key($this->properties);
    }

    public function valid(): bool
    {
        return \key($this->properties) !== null;
    }

    public function rewind(): void
    {
        \reset($this->properties);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->properties[$offset]);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->properties[] = $value;
        } else {
            $this->properties[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->properties[$offset]);
    }
}
