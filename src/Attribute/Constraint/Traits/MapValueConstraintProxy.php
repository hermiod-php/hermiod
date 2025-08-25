<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\ArrayConstraintInterface;
use Hermiod\Attribute\Constraint\NumberConstraintInterface;
use Hermiod\Attribute\Constraint\ObjectValueConstraintInterface;
use Hermiod\Attribute\Constraint\StringConstraintInterface;
use Hermiod\Resource\Path\PathInterface;

trait MapValueConstraintProxy
{
    private NumberConstraintInterface | StringConstraintInterface $constraint;

    private ArrayConstraintInterface | ObjectValueConstraintInterface $validator;

    public function mapValueMatchesConstraint(mixed $value): bool
    {
        if (!$this->validator->mapValueMatchesConstraint($value)) {
            return false;
        }

        /**
         * We're passing broad types to specific types here for the sake of code reuse.
         * @phpstan-ignore argument.type
         */
        return $this->constraint->valueMatchesConstraint($value);
    }

    public function getMismatchExplanation(PathInterface $path, mixed $value): string
    {
        if (!$this->validator->mapValueMatchesConstraint($value)) {
            return $this->validator->getMismatchExplanation($path, $value);
        }

        /**
         * We're passing broad types to specific types here for the sake of code reuse.
         * @phpstan-ignore argument.type
         */
        return $this->constraint->getMismatchExplanation($path, $value);
    }
}