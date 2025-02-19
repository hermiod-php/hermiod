<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Traits\MapValueNumberInList;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ObjectValueNumberInList implements ObjectConstraintInterface
{
    use MapValueNumberInList;
}
