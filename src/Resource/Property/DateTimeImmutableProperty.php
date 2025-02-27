<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Resource\Property\Exception\InvalidDateTimeTypeException;
use Hermiod\Resource\Property\Exception\InvalidDateTimeValueException;

final class DateTimeImmutableProperty implements PropertyInterface
{
    use Traits\ConstructWithNameAndNullableTrait;
    use Traits\Iso8601DateTimeStringConstraints;

    public function getDefaultValue(): null
    {
        return null;
    }

    public function hasDefaultValue(): bool
    {
        return false;
    }

    public function convertToPhpValue(mixed $value): \DateTimeImmutable
    {
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value);
        }

        if (\is_string($value)) {
            try {
                return new \DateTimeImmutable($value);
            } catch (\Throwable $exception) {
                throw InvalidDateTimeValueException::new($value, $exception);
            }
        }

        throw InvalidDateTimeTypeException::new($value);
    }

    public function convertToJsonValue(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if (\is_string($value)) {
            return $value;
        }

        throw InvalidDateTimeTypeException::new($value);
    }
}
