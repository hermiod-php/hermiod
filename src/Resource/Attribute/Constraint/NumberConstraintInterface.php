<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Attribute\Constraint;

use JsonObjectify\Resource\Path\PathInterface;

interface NumberConstraintInterface
{
    public function valueMatchesConstraint(int|float $value): bool;

    public function getMismatchExplanation(PathInterface $path, int|float $value): string;
}