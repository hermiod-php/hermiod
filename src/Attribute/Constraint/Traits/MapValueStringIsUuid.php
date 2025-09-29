<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\StringIsUuid;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Traits\JsonCompatibleTypeName;

trait MapValueStringIsUuid
{
    use JsonCompatibleTypeName;

    private StringIsUuid $validator;

    public function mapValueMatchesConstraint(mixed $value): bool
    {
        if (\is_string($value)) {
            return $this->getEmailValidator()->valueMatchesConstraint($value);
        }

        return false;
    }

    public function getMismatchExplanation(PathInterface $path, mixed $value): string
    {
        return \sprintf(
            "%s must be a UUID string but %s given",
            $path->__toString(),
            \is_string($value) ? "'$value'" : $this->getTypeName($value),
        );
    }

    private function getEmailValidator(): StringIsUuid
    {
        return $this->validator ??= new StringIsUuid();
    }
}
