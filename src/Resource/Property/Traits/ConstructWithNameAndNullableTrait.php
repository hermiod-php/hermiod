<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Traits;

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