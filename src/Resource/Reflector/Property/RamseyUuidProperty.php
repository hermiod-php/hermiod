<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property;

use Hermiod\Attribute\Constraint\StringConstraintInterface;
use Hermiod\Attribute\Constraint\StringIsUuid;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Reflector\Property\Exception\InvalidUuidTypeException;
use Hermiod\Resource\Reflector\Property\Exception\InvalidUuidValueException;
use Ramsey\Uuid\Exception\UuidExceptionInterface;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;

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

    public function convertToPhpValue(mixed $value): UuidInterface
    {
        if ($value instanceof UuidInterface) {
            return $value;
        }

        if (\is_string($value)) {
            try {
                return Uuid::fromString($value);
            } catch (UuidExceptionInterface $exception) {
                throw InvalidUuidValueException::new($value, $exception);
            }
        }

        throw InvalidUuidTypeException::new($value);
    }

    public function convertToJsonValue(mixed $value): string
    {
        if ($value instanceof UuidInterface) {
            return $value->toString();
        }

        if (\is_string($value)) {
            return $value;
        }

        throw InvalidUuidTypeException::new($value);
    }
}
