<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Traits\MapValueStringIsEmail;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ObjectValueStringIsEmail implements ObjectValueConstraintInterface
{
    use MapValueStringIsEmail;
}
