<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\NumberConstraintInterface;
use Hermiod\Attribute\Constraint\StringConstraintInterface;
use Hermiod\Resource\Path\PathInterface;

trait MapValueConstraintProxy
{
    private NumberConstraintInterface | StringConstraintInterface $constraint;

    public function mapValueMatchesConstraint(mixed $value): bool
    {
        return $this->constraint->valueMatchesConstraint($value);
    }

    public function getMismatchExplanation(PathInterface $path, mixed $value): string
    {
        return $this->constraint->getMismatchExplanation($path, $value);
    }
}