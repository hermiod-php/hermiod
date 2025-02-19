<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Traits\MapValueIsFloat;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ArrayValueIsFloat implements ArrayConstraintInterface
{
    use MapValueIsFloat;
}
