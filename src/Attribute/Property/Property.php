<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Property;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Property implements PropertyInterface
{
    public function __construct(
        private ?string $name = null,
        private ?string $concrete = null,
    )
    {
        if ($this->concrete !== null && !\class_exists($this->concrete)) {
            throw new \Exception();
        }

        if ($this->name === null) {
            return;
        }

        $this->name = \trim($name);

        if (empty($this->name)) {
            throw new \InvalidArgumentException('Property name cannot be empty.');
        }
    }

    public function getNameOverride(): ?string
    {
        return $this->name;
    }

    public function getConcrete(): ?string
    {
        return $this->concrete;
    }
}
