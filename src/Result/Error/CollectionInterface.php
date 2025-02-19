<?php

declare(strict_types=1);

namespace Hermiod\Result\Error;

use Traversable;

interface CollectionInterface extends \IteratorAggregate, \Countable, \JsonSerializable
{
    public function jsonSerialize(): array;

    /**
     * @return Traversable<ErrorInterface>
     */
    public function getIterator(): Traversable;
}
