<?php

declare(strict_types=1);

namespace Hermiod\Result\Error;

use Traversable;

/**
 * @extends \IteratorAggregate<int, ErrorInterface>
 */
interface CollectionInterface extends \IteratorAggregate, \Countable, \JsonSerializable
{
    /**
     * @return ErrorInterface[]
     */
    public function jsonSerialize(): array;

    /**
     * @return Traversable<ErrorInterface>
     */
    public function getIterator(): Traversable;
}
