<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property;

use Hermiod\Resource\Path\PathInterface;

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

        if ($value instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($value);
        }

        if (\is_string($value)) {
            return new \DateTimeImmutable($value);
        }

        throw new \InvalidArgumentException('Expected a DateTime or \DateTimeImmutable');
    }

    public function convertToJsonValue(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        throw new \InvalidArgumentException('Expected a DateTime or \DateTimeInterface');
    }
}
