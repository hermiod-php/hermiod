<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\StringMatchesRegex;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Traits\JsonCompatibleTypeName;

trait MapValueStringMatchesRegex
{
    use JsonCompatibleTypeName;

    private StringMatchesRegex $validator;

    private string $expression;

    public function __construct(string $regex)
    {
        $this->expression = $regex;
        $this->validator = new StringMatchesRegex($regex);
    }

    public function mapValueMatchesConstraint(mixed $value): bool
    {
        if (\is_string($value)) {
            return $this->validator->valueMatchesConstraint($value);
        }

        return false;
    }

    public function getMismatchExplanation(PathInterface $path, mixed $value): string
    {
        return \sprintf(
            "%s must be a string matching the regex '%s' but %s given",
            $path->__toString(),
            $this->expression,
            \is_string($value) ? "'$value'" : $this->getTypeName($value),
        );
    }
}