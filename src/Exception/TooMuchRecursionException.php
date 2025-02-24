<?php

declare(strict_types=1);

namespace Hermiod\Exception;

use Hermiod\Exception\Exception;

final class TooMuchRecursionException extends \OverflowException implements Exception
{
    public static function new(int $maxDepth): self
    {
        return new self(
            \sprintf(
                'Exceeded the maximum object depth of %d nested objects',
                $maxDepth,
            )
        );
    }
}
