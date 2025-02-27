<?php

declare(strict_types=1);

namespace Hermiod\Resource\Path\Exception;

use Hermiod\Resource\Path\PathInterface;

final class EmptyJsonPathObjectKeyException extends \DomainException implements Exception
{
    public static function new(PathInterface $root, string $key): self
    {
        return new self(
            \sprintf(
                "The JSON object key '%s' could not be appended to %s as it is empty.",
                \trim((string)\json_encode($key), '"'),
                $root->__toString(),
            )
        );
    }
}
