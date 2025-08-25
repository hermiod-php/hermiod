<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute;

use Hermiod\Attribute\Exception\IncludeFlagOutOfRangeException;
use Hermiod\Attribute\Resource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Resource::class)]
final class ResourceTest extends TestCase
{
    public function testDefaults(): void
    {
        $resource = new Resource();

        $this->assertSame(
            Resource::INCLUDE_ALL_PROPERTIES,
            $resource->getReflectionPropertyFilter(),
            'Default filter should include all properties.'
        );

        $this->assertTrue(
            $resource->canAutoSerialize(),
            'Default autoSerialize should be true.'
        );
    }

    #[DataProvider('provideValidIncludeValues')]
    public function testGetReflectionPropertyFilterReturnsExpectedValue(int $input, int $expected): void
    {
        $resource = new Resource($input);
        $this->assertSame($expected, $resource->getReflectionPropertyFilter(), 'Filter should match expected input.');
    }

    #[DataProvider('provideInvalidIncludeValues')]
    public function testThrowsExceptionForInvalidIncludeFlag(int $invalidFlag): void
    {
        $this->expectException(IncludeFlagOutOfRangeException::class);
        new Resource($invalidFlag);
    }

    public function testCanAutoSerializeFalse(): void
    {
        $resource = new Resource(Resource::INCLUDE_PUBLIC_PROPERTIES, false);

        $this->assertFalse(
            $resource->canAutoSerialize(),
            'canAutoSerialize should return false when set.',
        );
    }

    public static function provideValidIncludeValues(): \Generator
    {
        yield 'explicit only' => [Resource::INCLUDE_EXPLICIT_PROPERTIES_ONLY, 0];
        yield 'public'        => [Resource::INCLUDE_PUBLIC_PROPERTIES, \ReflectionProperty::IS_PUBLIC];
        yield 'protected'     => [Resource::INCLUDE_PROTECTED_PROPERTIES, \ReflectionProperty::IS_PROTECTED];
        yield 'private'       => [Resource::INCLUDE_PRIVATE_PROPERTIES, \ReflectionProperty::IS_PRIVATE];
        yield 'all'           => [Resource::INCLUDE_ALL_PROPERTIES, Resource::INCLUDE_ALL_PROPERTIES];
    }

    public static function provideInvalidIncludeValues(): \Generator
    {
        yield 'negative'     => [-1];
        yield 'too large'    => [8];
        yield 'nonsense bit' => [0b1000];
    }
}
