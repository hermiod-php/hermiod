<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Traits;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
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