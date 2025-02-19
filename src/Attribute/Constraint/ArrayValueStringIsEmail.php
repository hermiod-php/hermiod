<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Traits\MapValueStringIsEmail;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ArrayValueStringIsEmail implements ArrayConstraintInterface
{
    public function __construct()
    {
        $this->constraint = new StringIsUuid();
    }
}
