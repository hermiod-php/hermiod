<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Traits;

use Hermiod\Resource\Property\Exception\InvalidPropertyNameException;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
trait GetPropertyNameTrait
{
    private const VALID_PHP_PROPERTY_NAME = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/D';

    private string $name;

    private function setName(string $name): void
    {
        $name = \trim($name);

        if (!\preg_match(self::VALID_PHP_PROPERTY_NAME, $name)) {
            throw InvalidPropertyNameException::new($name);
        }

        $this->name = $name;
    }

    public function getPropertyName(): string
    {
        return $this->name;
    }
}
