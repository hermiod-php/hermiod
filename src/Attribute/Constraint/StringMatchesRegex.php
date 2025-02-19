<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Resource\Path\PathInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class StringMatchesRegex implements StringConstraintInterface
{
    private string $expression;

    public function __construct(string $regex)
    {
        $this->expression = $regex;
    }

    public function valueMatchesConstraint(string $value): bool
    {
        return (bool) \preg_match($this->expression, $value);
    }

    public function getMismatchExplanation(PathInterface $path, mixed $value): string
    {
        return \sprintf(
            "%s must must match regex '%s' but '%s' given",
            $path->__toString(),
            $this->expression,
            $value,
        );
    }
}
