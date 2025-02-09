<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector\Property\Traits;

use JsonObjectify\Resource\Reflector\Property\Constraint;

trait ConstructWithNameAndNullableTrait
{
   use GetPropertyNameTrait;

    public function __construct(
        string $name,
        private bool $nullable
    )
    {
        $this->setName($name);
    }
}