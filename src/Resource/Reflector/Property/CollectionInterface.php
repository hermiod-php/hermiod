<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector\Property;

interface CollectionInterface extends \Iterator, \ArrayAccess
{
    public function offsetGet(mixed $offset): ?PropertyInterface;

    public function current(): ?PropertyInterface;
}