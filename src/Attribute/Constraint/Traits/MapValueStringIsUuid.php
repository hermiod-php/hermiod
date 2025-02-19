<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\StringIsUuid;

trait MapValueStringIsUuid
{
    use MapValueConstraintProxy;

    public function __construct()
    {
        $this->constraint = new StringIsUuid();
    }
}