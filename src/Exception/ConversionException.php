<?php

declare(strict_types=1);

namespace Hermiod\Exception;

use Hermiod\Result\Error\Collection;
use Hermiod\Result\Error\CollectionInterface;
use Hermiod\Result\Error\ErrorInterface;

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
                'Invalid %s in JSON structure. %s',
                \count($errors) === 1 ? 'property' : 'properties',
                \implode(
                    '. ',
                    \array_map(
                        fn (ErrorInterface $error) => \json_encode($error, \JSON_THROW_ON_ERROR),
                        \iterator_to_array($errors)
                    )
                )
            )
        );

        $exception->errors = $errors;

        return $exception;
    }

    public static function dueToUnparsableJsonException(Exception $previous): self
    {
        $exception = new self(
            $previous->getMessage(),
            $previous->getCode(),
            $previous,
        );

        $exception->errors = Collection::fromThrowable($previous);

        return $exception;
    }

    public function getErrors(): CollectionInterface
    {
        return $this->errors;
    }
}
