<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Resource\Path\PathInterface;

interface ObjectConstraintInterface
{
    public function mapValueMatchesConstraint(mixed $value): bool;

    public function getMismatchExplanation(PathInterface $path, array $value): string;
}