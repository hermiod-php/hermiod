<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector\Property;

final class Collection implements CollectionInterface
{
    private array $hash;
    private array $list;

    public function __construct(PropertyInterface ...$properties)
    {
        foreach ($properties as $property) {
            $this->hash[$property->getPropertyName()] = $property;
        }

        $this->list = $properties;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->hash[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet(mixed $offset): ?PropertyInterface
    {
        return $this->hash[$offset] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \RuntimeException();
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new \RuntimeException();
    }

    public function current(): ?PropertyInterface
    {
        return \current($this->list);
    }

    public function next(): void
    {
        \next($this->list);
    }

    public function key(): string
    {
        return $this->list[\key($this->list)]?->getPropertyName();
    }

    public function valid(): bool
    {
        return \key($this->list) !== null;
    }

    public function rewind(): void
    {
        \reset($this->list);
    }
}
