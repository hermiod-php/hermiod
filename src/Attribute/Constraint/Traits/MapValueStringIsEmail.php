<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\StringIsEmail;

trait MapValueStringIsEmail
{
    use MapValueConstraintProxy;

    public function __construct()
    {
        $this->constraint = new StringIsEmail();
    }
}