<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

/**
 * @extends \ArrayAccess<string, PropertyInterface>
 * @extends \Iterator<string, PropertyInterface>
 *
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
interface CollectionInterface extends \Iterator, \ArrayAccess
{
    public function offsetGet(mixed $offset): ?PropertyInterface;

    public function current(): ?PropertyInterface;
}