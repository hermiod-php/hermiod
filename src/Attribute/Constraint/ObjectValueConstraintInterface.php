<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Resource\Path\PathInterface;

interface ObjectValueConstraintInterface extends ConstraintInterface
{
    public function mapValueMatchesConstraint(mixed $value): bool;

    public function getMismatchExplanation(PathInterface $path, mixed $value): string;
}