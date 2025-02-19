<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\NumberInList;

trait MapValueNumberInList
{
    use MapValueConstraintProxy;

    public function __construct(int|float $value, int|float ...$values)
    {
        $this->constraint = new NumberInList($value, ...$values);
    }
}