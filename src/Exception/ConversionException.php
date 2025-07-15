<?php

declare(strict_types=1);

namespace Hermiod\Exception;

use Hermiod\Result\Error\CollectionInterface;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class ConversionException extends \DomainException implements Exception
{
    private CollectionInterface $errors;

    public static function dueToTranspositionErrors(CollectionInterface $errors): self
    {
        $exception = new self(
            \sprintf(
                'Invalid %s in JSON structure',
                \count($errors) === 1 ? 'property' : 'properties',
            )
        );

        $exception->errors = $errors;

        return $exception;
    }

    public function getErrors(): CollectionInterface
    {
        return $this->errors;
    }
}
