<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector\Property\Traits;

trait GetPropertyNameTrait
{
    private const VALID_PHP_PROPERTY_NAME = '/^.+$/';

    private string $name;

    private function setName(string $name): void
    {
        $name = \trim($name);

        if (!\preg_match(self::VALID_PHP_PROPERTY_NAME, $name)) {
            throw new \InvalidArgumentException("Property name '{$name}' is not valid");
        }

        $this->name = $name;
    }

    public function getPropertyName(): string
    {
        return $this->name;
    }
}