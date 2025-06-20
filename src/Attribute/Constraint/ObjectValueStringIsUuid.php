<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Traits\MapValueStringIsUuid;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ObjectValueStringIsUuid implements ObjectValueConstraintInterface
{
    use MapValueStringIsUuid;
}
