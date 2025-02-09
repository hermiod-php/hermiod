<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Attribute\Constraint;

use JsonObjectify\Resource\Path\PathInterface;

interface StringConstraintInterface
{
    public function valueMatchesConstraint(string $value): bool;

    public function getMismatchExplanation(PathInterface $path, string $value): string;
}