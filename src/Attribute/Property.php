<?php

declare(strict_types=1);

namespace Hermiod\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Property implements PropertyInterface
{
    public function __construct(
        private ?string $name = null,
    )
    {
        if ($this->name === null) {
            return;
        }

        $this->name = \trim($this->name);

        if (\strlen($this->name) === 0) {
            throw new \InvalidArgumentException('Property name cannot be empty.');
        }
    }

    public function getNameOverride(): ?string
    {
        return $this->name;
    }
}
