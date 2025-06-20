<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Resource\Path\PathInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ObjectKeyStringMatchesRegex implements ObjectKeyConstraintInterface
{
    private StringConstraintInterface $constraint;

    public function __construct(
        private readonly string $regex,
    )
    {
        $this->constraint = new StringMatchesRegex($regex);
    }

    public function mapKeyMatchesConstraint(string $key): bool
    {
        return $this->constraint->valueMatchesConstraint($key);
    }

    public function getMismatchExplanation(PathInterface $path, string $key): string
    {
        return \sprintf(
            'All keys of %s must match the regex %s but "%s" given',
            $path->__toString(),
            $this->regex,
            $key,
        );
    }
}
