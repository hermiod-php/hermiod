<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Resource\Path\PathInterface;

interface ObjectKeyConstraintInterface
{
    public function mapKeyMatchesConstraint(string $key): bool;

    public function getMismatchExplanation(PathInterface $path, string $key): string;
}