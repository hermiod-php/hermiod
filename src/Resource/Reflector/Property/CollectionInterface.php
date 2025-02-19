<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property;

/**
 * @extends \ArrayAccess<string, PropertyInterface>
 * @extends \Iterator<string, PropertyInterface>
 */
interface CollectionInterface extends \Iterator, \ArrayAccess
{
    public function offsetGet(mixed $offset): ?PropertyInterface;

    public function current(): ?PropertyInterface;
}