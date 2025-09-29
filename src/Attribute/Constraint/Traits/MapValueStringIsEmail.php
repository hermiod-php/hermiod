<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\StringIsEmail;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Traits\JsonCompatibleTypeName;

trait MapValueStringIsEmail
{
    use JsonCompatibleTypeName;

    private StringIsEmail $validator;

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
            "%s must be an email address string but %s given",
            $path->__toString(),
            \is_string($value) ? "'$value'" : $this->getTypeName($value),
        );
    }

    private function getEmailValidator(): StringIsEmail
    {
        return $this->validator ??= new StringIsEmail();
    }
}
