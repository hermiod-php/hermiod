<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Resource\Property\Exception\AddingToSealedCollectionException;
use Hermiod\Resource\Property\Exception\DeletingFromSealedCollectionException;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class Collection implements CollectionInterface
{
    /**
     * @var array<string, PropertyInterface>
     */
    private array $hash = [];

    /**
     * @var PropertyInterface[]
     */
    private array $list;

    public function __construct(PropertyInterface ...$properties)
    {
        foreach ($properties as $property) {
            $this->hash[\strtolower($property->getPropertyName())] = $property;
        }

        $this->list = $properties;
    }

    /**
     * @inheritDoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->hash[\strtolower($offset)]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet(mixed $offset): ?PropertyInterface
    {
        return $this->hash[\strtolower($offset)] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw AddingToSealedCollectionException::new($this, $offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset(mixed $offset): void
    {
        throw DeletingFromSealedCollectionException::new($this, $offset);
    }

    public function current(): ?PropertyInterface
    {
        $property = \current($this->list);

        return $property === false ? null : $property;
    }

    public function next(): void
    {
        \next($this->list);
    }

    public function key(): ?string
    {
        $key = \key($this->list);

        if (null === $key) {
            return null;
        }

        return $this->list[$key]->getPropertyName();
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
