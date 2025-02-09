<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Attribute\Constraint;

use JsonObjectify\Resource\Path\PathInterface;

interface ArrayConstraintInterface
{
    public function valueMatchesConstraint(array $value): bool;

    public function getMismatchExplanation(PathInterface $path, array $value): string;
}