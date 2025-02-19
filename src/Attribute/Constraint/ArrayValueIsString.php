<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Traits\MapValueIsString;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ArrayValueIsString implements ArrayConstraintInterface
{
    use MapValueIsString;
}
