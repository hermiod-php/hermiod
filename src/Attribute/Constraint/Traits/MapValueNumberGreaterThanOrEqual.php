<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\NumberGreaterThanOrEqual;

trait MapValueNumberGreaterThanOrEqual
{
    use MapValueConstraintProxy;

    public function __construct(int|float $value)
    {
        $this->constraint = new NumberGreaterThanOrEqual($value);
    }
}