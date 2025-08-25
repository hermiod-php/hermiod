<?php

declare(strict_types=1);

namespace Hermiod\Attribute;

use Hermiod\Attribute\Exception\IncludeFlagOutOfRangeException;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Resource implements ResourceInterface
{
    public const INCLUDE_PUBLIC_PROPERTIES = \ReflectionProperty::IS_PUBLIC;
    public const INCLUDE_PROTECTED_PROPERTIES = \ReflectionProperty::IS_PROTECTED;
    public const INCLUDE_PRIVATE_PROPERTIES = \ReflectionProperty::IS_PRIVATE;
    public const INCLUDE_ALL_PROPERTIES = self::INCLUDE_PUBLIC_PROPERTIES | self::INCLUDE_PROTECTED_PROPERTIES | self::INCLUDE_PRIVATE_PROPERTIES;
    public const INCLUDE_EXPLICIT_PROPERTIES_ONLY = 0b000;

    public function __construct(
        private int $include = self::INCLUDE_ALL_PROPERTIES,
        private bool $autoSerialize = true,
    )
    {
        if ($include < self::INCLUDE_EXPLICIT_PROPERTIES_ONLY || $include > self::INCLUDE_ALL_PROPERTIES) {
            throw IncludeFlagOutOfRangeException::forSuppliedValue($this, $include);
        }
    }

    public function getReflectionPropertyFilter(): int
    {
        return $this->include;
    }

    public function canAutoSerialize(): bool
    {
        return $this->autoSerialize;
    }
}
