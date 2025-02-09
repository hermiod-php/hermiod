<?php

declare(strict_types=1);

namespace JsonObjectify\Result\Error;

use Traversable;
use JsonObjectify\Resource\Reflector\Property;

final class Collection implements CollectionInterface
{
    private array $errors = [];

    public static function fromPropertyValidationResult(Property\Validation\ResultInterface $result): self
    {
        return new self(
            ...\array_map(
                static fn (string $error): ErrorInterface => new Error($error),
                $result->getValidationErrors(),
            )
        );
    }

    public function __construct(ErrorInterface ...$error)
    {
        $this->errors = $error;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->errors);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return \count($this->errors);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return $this->errors;
    }
}
