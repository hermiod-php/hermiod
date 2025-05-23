<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\NumberLessThanOrEqual;

trait MapValueNumberLessThanOrEqual
{
    use MapValueConstraintProxy;

    public function __construct(int|float $value)
    {
        $this->constraint = new NumberLessThanOrEqual($value);
    }
}