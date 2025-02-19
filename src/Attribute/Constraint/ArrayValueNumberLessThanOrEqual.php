<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Traits\MapValueConstraintProxy;
use Hermiod\Attribute\Constraint\Traits\MapValueNumberLessThanOrEqual;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ArrayValueNumberLessThanOrEqual implements ArrayConstraintInterface
{
    use MapValueNumberLessThanOrEqual;
}
