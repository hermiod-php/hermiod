<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Traits\MapValueNumberGreaterThan;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ArrayValueNumberGreaterThan implements ArrayConstraintInterface
{
    use MapValueNumberGreaterThan;
}
