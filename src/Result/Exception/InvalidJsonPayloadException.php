<?php

declare(strict_types=1);

namespace Hermiod\Result\Exception;

use Hermiod\Exception\Exception;

final class InvalidJsonPayloadException extends \DomainException implements Exception
{
    /**
     * @param string $class
     * @param array<string> $errors
     */
    public static function new(string $class, array $errors): self
    {
        return new self(
            \sprintf(
                "Unable to create instance of %s as the supplied JSON was not valid. Errors:\n%s",
                $class,
                \implode("\n", $errors),
            )
        );
    }
}
