<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Traits\MapValueNumberInList;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ArrayValueNumberInList implements ArrayConstraintInterface
{
    use MapValueNumberInList;
}
