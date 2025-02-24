<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Resource\Path\PathInterface;

interface StringConstraintInterface extends ConstraintInterface
{
    public function valueMatchesConstraint(string $value): bool;

    public function getMismatchExplanation(PathInterface $path, string $value): string;
}