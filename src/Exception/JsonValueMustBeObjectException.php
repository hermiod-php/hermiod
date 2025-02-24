<?php

declare(strict_types=1);

namespace Hermiod\Exception;

use Hermiod\Exception\Exception;

final class JsonValueMustBeObjectException extends \InvalidArgumentException implements Exception
{
    public static function new(mixed $actual): self
    {
        return new self(
            \sprintf(
                'JSON string must decode to an object, but %s given.',
                \gettype($actual),
            )
        );
    }
}
