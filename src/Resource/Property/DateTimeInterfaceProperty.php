<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use DateTimeImmutable;
use Hermiod\Resource\Property\Exception\InvalidDateTimeTypeException;
use Hermiod\Resource\Property\Exception\InvalidDateTimeValueException;
use Hermiod\Resource\Property\Exception\InvalidDefaultValueException;

final class DateTimeInterfaceProperty implements PropertyInterface
{
    use Traits\ConstructWithNameAndNullableTrait;
    use Traits\Iso8601DateTimeStringConstraints;

    private bool $hasDefault = false;

    public static function withDefaultNullValue(string $name, bool $nullable): self
    {
        $property = new self($name, $nullable);

        $property->hasDefault = true;

        return $property;
    }

    public function getDefaultValue(): null
    {
        return null;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefault;
    }

    public function normalisePhpValue(mixed $value): string
    {
        return $this->normalise($value);
    }

    public function normaliseJsonValue(mixed $value): string
    {
        return $this->normalise($value);
    }

    private function normalise(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if (\is_string($value)) {
            try {
                return (new \DateTimeImmutable($value))->format(\DateTimeInterface::ATOM);
            } catch (\Throwable $exception) {
                throw InvalidDateTimeValueException::new($value, $exception);
            }
        }

        throw InvalidDateTimeTypeException::new($value);
    }
}
