<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Resource\Path\PathInterface;

interface NumberConstraintInterface
{
    public function valueMatchesConstraint(int|float $value): bool;

    public function getMismatchExplanation(PathInterface $path, int|float $value): string;
}