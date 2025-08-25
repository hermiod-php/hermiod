<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\ArrayValueIsFloat;
use Hermiod\Attribute\Constraint\NumberGreaterThan;

trait MapValueNumberGreaterThan
{
    use MapValueConstraintProxy;

    public function __construct(int|float $value)
    {
        $this->validator = new ArrayValueIsFloat();
        $this->constraint = new NumberGreaterThan($value);
    }
}