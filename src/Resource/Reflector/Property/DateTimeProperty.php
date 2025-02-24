<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Reflector\Property\Exception\InvalidDateTimeTypeException;
use Hermiod\Resource\Reflector\Property\Exception\InvalidDateTimeValueException;

final class DateTimeProperty implements PropertyInterface
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

    public function convertToPhpValue(mixed $value): \DateTime
    {
        if ($value instanceof \DateTime) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return \DateTime::createFromInterface($value);
        }

        if (\is_string($value)) {
            try {
                return new \DateTime($value);
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
