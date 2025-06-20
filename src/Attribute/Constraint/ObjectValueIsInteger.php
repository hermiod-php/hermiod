<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Traits\MapValueIsInteger;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ObjectValueIsInteger implements ObjectValueConstraintInterface
{
    use MapValueIsInteger;
}
