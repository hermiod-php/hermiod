<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Traits\MapValueNumberGreaterThanOrEqual;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ArrayValueNumberGreaterThanOrEqual implements ArrayConstraintInterface
{
    use MapValueNumberGreaterThanOrEqual;
}
