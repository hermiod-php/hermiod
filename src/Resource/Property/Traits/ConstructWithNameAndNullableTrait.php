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

    private bool $nullable;

    public function __construct(
        string $name,
        bool $nullable,
    )
    {
        $this->setName($name);

        $this->nullable = $nullable;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }
}