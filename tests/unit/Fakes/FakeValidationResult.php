<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Fakes;

use Hermiod\Resource\Hydrator\HydratorInterface;
use Hermiod\Resource\Property\Validation\ResultInterface;

class FakeValidationResult implements ResultInterface
{
    public function isValid(): bool
    {
        return true;
    }

    public function getValidationErrors(): array
    {
        return [];
    }

    public function withErrors(string ...$errors): ResultInterface
    {
        return $this;
    }

    public function withHydrationStage(callable $callback): ResultInterface
    {
        return $this;
    }

    public function hydrate(HydratorInterface $hydrator): void
    {
        // TODO: Implement hydrate() method.
    }

    public function merge(ResultInterface $result): ResultInterface
    {
        return $this;
    }
}

