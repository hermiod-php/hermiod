<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Traits\MapValueStringMatchesRegex;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ArrayValueStringMatchesRegex implements ArrayConstraintInterface
{
    use MapValueStringMatchesRegex;
}
