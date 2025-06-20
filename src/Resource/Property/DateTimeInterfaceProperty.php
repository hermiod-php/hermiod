<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\Exception\InvalidDateTimeTypeException;
use Hermiod\Resource\Property\Exception\InvalidDateTimeValueException;
use Hermiod\Resource\Property\Validation\ResultInterface;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 *
 * TODO: Add support for other date extensions like Laravel Carbon and Symfony DatePoint
 */
final class DateTimeInterfaceProperty implements PropertyInterface
{
    use Traits\ConstructWithNameAndNullableTrait;

    private const ISO_8601_FORMAT_WITH_MILLISECONDS = 'Y-m-d\TH:i:s.vP';
    private const ISO_8601_DATE_TIME_STRING = '/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';

    private bool $hasDefault = false;

    public static function withDefaultNullValue(string $name): self
    {
        $property = new self($name, true);

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

    public function checkValueAgainstConstraints(PathInterface $path, mixed $value): Validation\ResultInterface
    {
        if ($value instanceof \DateTimeInterface) {
            return new Validation\Result();
        }

        if ($value === null && $this->nullable) {
            return new Validation\Result();
        }

        if (!\is_string($value)) {
            return $this->error($path, $value);
        }

        if (!\preg_match(self::ISO_8601_DATE_TIME_STRING, $value)) {
            return $this->error($path, $value);
        }

        return new Validation\Result();
    }

    public function normalisePhpValue(mixed $value): ?\DateTimeInterface
    {
        return $this->normalise($value);
    }

    public function normaliseJsonValue(mixed $value): ?string
    {
        $normalised = $this->normalise($value);

        return $normalised ? $this->format($normalised) : null;
    }

    private function normalise(mixed $value): ?\DateTimeInterface
    {
        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        if ($value === null && $this->nullable) {
            return null;
        }

        if (!\is_string($value)) {
            throw InvalidDateTimeTypeException::new($value);
        }

        $length = \strlen($value);

        if ($length < 4) {
            throw InvalidDateTimeValueException::new($value);
        }

        if ($length === 4 && \is_numeric($value)) {
            $value = \sprintf('%d-01-01T00:00:00', $value);
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable $exception) {
            throw InvalidDateTimeValueException::new($value, $exception);
        }
    }

    private function format(\DateTimeInterface $date): string
    {
        $format = $date->format('v') === '000'
            ? \DateTimeInterface::ATOM
            : self::ISO_8601_FORMAT_WITH_MILLISECONDS;

        return $date->format($format);
    }

    private function error(PathInterface $path, mixed $value): ResultInterface
    {
        return new Validation\Result(
            \sprintf(
                '%s must be an valid ISO8601 date-time string but %s given',
                $path->__toString(),
                \is_string($value) ? \sprintf("'%s'", $value) : \get_debug_type($value),
            )
        );
    }
}
