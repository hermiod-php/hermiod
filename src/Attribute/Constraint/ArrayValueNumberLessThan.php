<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Traits\MapValueNumberLessThan;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ArrayValueNumberLessThan implements ArrayConstraintInterface
{
    use MapValueNumberLessThan;
}
