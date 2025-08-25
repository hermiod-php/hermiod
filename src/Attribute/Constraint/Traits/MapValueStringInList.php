<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\ArrayValueIsString;
use Hermiod\Attribute\Constraint\StringInList;

trait MapValueStringInList
{
    use MapValueConstraintProxy;

    public function __construct(string $value, string ...$values)
    {
        $this->validator = new ArrayValueIsString();
        $this->constraint = new StringInList($value, ...$values);
    }
}