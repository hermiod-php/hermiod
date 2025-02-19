<?php

declare(strict_types=1);

namespace Hermiod\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Resource implements ResourceInterface
{
    public const INCLUDE_PUBLIC = 0b001;
    public const INCLUDE_PROTECTED = 0b010;
    public const INCLUDE_PRIVATE = 0b100;
    public const INCLUDE_ALL = self::INCLUDE_PUBLIC | self::INCLUDE_PROTECTED | self::INCLUDE_PRIVATE;
    public const INCLUDE_EXPLICIT_ONLY = 0b000;

    public function __construct(
        private int $properties = self::INCLUDE_ALL,
    ) {}

    public function autoIncludePublicProperties(): bool
    {
        return (self::INCLUDE_PUBLIC & $this->properties) === 0;
    }

    public function autoIncludeProtectedProperties(): bool
    {
        return (self::INCLUDE_PROTECTED & $this->properties) === 0;
    }

    public function autoIncludePrivateProperties(): bool
    {
        return (self::INCLUDE_PRIVATE & $this->properties) === 0;
    }
}
