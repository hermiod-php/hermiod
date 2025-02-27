<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Traits;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\Validation;

trait Iso8601DateTimeStringConstraints
{
    private const ISO_8601_DATE_TIME_STRING = '/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';

    public function checkValueAgainstConstraints(PathInterface $path, mixed $value): Validation\ResultInterface
    {
        if (!\is_string($value)) {
            return new Validation\Result(
                \sprintf(
                    '%s must be an ISO8601 date-time string but %s given',
                    $path->__toString(),
                    \gettype($value),
                )
            );
        }

        if (!\preg_match(self::ISO_8601_DATE_TIME_STRING, $value)) {
            return new Validation\Result(
                \sprintf(
                    "%s must be an valid ISO8601 date-time string but '%s' given",
                    $path->__toString(),
                    \gettype($value),
                )
            );
        }

        return new Validation\Result();
    }
}