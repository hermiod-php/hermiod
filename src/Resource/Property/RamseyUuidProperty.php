<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Attribute\Constraint\StringConstraintInterface;
use Hermiod\Attribute\Constraint\StringIsUuid;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\Exception\InvalidUuidTypeException;
use Hermiod\Resource\Property\Exception\InvalidUuidValueException;
use Ramsey\Uuid\Exception\UuidExceptionInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class RamseyUuidProperty implements PropertyInterface
{
    use Traits\ConstructWithNameAndNullableTrait;

    private StringConstraintInterface $constraint;

    public function getDefaultValue(): null
    {
        return null;
    }

    public function hasDefaultValue(): bool
    {
        return false;
    }

    public function checkValueAgainstConstraints(PathInterface $path, mixed $value): Validation\ResultInterface
    {
        if (!\is_string($value)) {
            return new Validation\Result(
                \sprintf(
                    '%s must be a UUID string but %s given',
                    $path->__toString(),
                    \gettype($value),
                )
            );
        }

        $constraint = $this->constraint ??= new StringIsUuid();

        if (!$constraint->valueMatchesConstraint($value)) {
            return new Validation\Result($constraint->getMismatchExplanation($path, $value));
        }

        return new Validation\Result();
    }

    public function normalisePhpValue(mixed $value): ?string
    {
        return $this->normalise($value);
    }

    public function normaliseJsonValue(mixed $value): ?string
    {
        return $this->normalise($value);
    }

    private function normalise(mixed $value): ?string
    {
        if ($value === null && $this->nullable) {
            return $value;
        }

        if ($value instanceof UuidInterface) {
            return $value->toString();
        }

        if (!\is_string($value)) {
            throw InvalidUuidTypeException::new($value);
        }

        if (!Uuid::isValid($value)) {
            throw InvalidUuidValueException::new($value);
        }

        return $value;
    }
}
