<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\StringMatchesRegex;

trait MapValueStringMatchesRegex
{
    use MapValueConstraintProxy;

    public function __construct(string $regex)
    {
        $this->constraint = new StringMatchesRegex($regex);
    }
}