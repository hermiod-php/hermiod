<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Traits\MapValueStringInList;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ArrayValueStringInList implements ArrayConstraintInterface
{
    use MapValueStringInList;
}
