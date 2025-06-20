<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Resource\Path\PathInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ObjectKeyStringIsUuid implements ObjectKeyConstraintInterface
{
    private StringConstraintInterface $constraint;

    public function mapKeyMatchesConstraint(string $key): bool
    {
        $this->constraint ??= new StringIsUuid();

        return $this->constraint->valueMatchesConstraint($key);
    }

    public function getMismatchExplanation(PathInterface $path, string $key): string
    {
        return \sprintf(
            'All keys of %s must be valid UUIDs but "%s" given',
            $path->__toString(),
            $key,
        );
    }
}
