<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\NumberGreaterThan;

trait MapValueNumberLessThan
{
    use MapValueConstraintProxy;

    public function __construct(int|float $value)
    {
        $this->constraint = new NumberGreaterThan($value);
    }
}