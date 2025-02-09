<?php

declare(strict_types=1);

namespace JsonObjectify\Tests\Integration\Resource\Reflector;

use JsonObjectify\Resource\Reflector\Property\CollectionInterface;
use JsonObjectify\Resource\Reflector\Property\Factory;
use JsonObjectify\Resource\Reflector\Property\IntegerProperty;
use JsonObjectify\Resource\Reflector\Property\MixedProperty;
use JsonObjectify\Resource\Reflector\Property\PropertyInterface;
use JsonObjectify\Resource\Reflector\Property\StringProperty;
use JsonObjectify\Resource\Reflector\Reflector;
use JsonObjectify\Tests\Integration\Fakes\IntegerPropertiesFake;
use JsonObjectify\Tests\Integration\Fakes\StringPropertiesFake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReflectorSuccessTest extends TestCase
{
    #[DataProvider('provideStringFakesPropertiesAndDefaults')]
    #[DataProvider('provideIntegerFakesPropertiesAndDefaults')]
    public function testCanParseTypedProperties(string $fake, string $name, string $class, bool $expectDefault, mixed $default = null): void
    {
        $reflector = new Reflector($fake, new Factory());

        $properties = $reflector->getProperties();

        $property = $this->getPropertyFromCollection($properties, $name, $fake);

        $this->assertInstanceOf(
            $class,
            $property,
            \sprintf('Property %s::%s was not parsed into a %s', $fake, $name, $class)
        );

        $this->assertSame($name, $property->getPropertyName());
        $this->assertSame($expectDefault, $property->hasDefaultValue());

        if ($expectDefault) {
            $this->assertSame($default, $property->getDefaultValue());
        }
    }

    private function getPropertyFromCollection(CollectionInterface $collection, string $name, string $fake): PropertyInterface
    {
        $this->assertArrayHasKey(
            $name,
            $collection,
            \sprintf('Property %s::%s not found', $fake, $name)
        );

        $property = $collection->offsetGet($name);

        $this->assertInstanceOf(
            PropertyInterface::class,
            $property,
            \sprintf('Property %s::%s was not parsed into a %s', $fake, $name, PropertyInterface::class)
        );

        return $property;
    }

    public static function provideStringFakesPropertiesAndDefaults(): \Generator
    {
        yield self::normalise('privateStringWithoutDefaultNotNullable') => [
            StringPropertiesFake::class,
            'privateStringWithoutDefaultNotNullable',
            StringProperty::class,
            false,
        ];

        yield self::normalise('protectedStringWithoutDefaultNotNullable') => [
            StringPropertiesFake::class,
            'protectedStringWithoutDefaultNotNullable',
            StringProperty::class,
            false,
        ];

        yield self::normalise('publicStringWithoutDefaultNotNullable') => [
            StringPropertiesFake::class,
            'protectedStringWithoutDefaultNotNullable',
            StringProperty::class,
            false,
        ];

        yield self::normalise('privateStringWithDefaultNotNullable') => [
            StringPropertiesFake::class,
            'privateStringWithDefaultNotNullable',
            StringProperty::class,
            true,
            'default',
        ];

        yield self::normalise('protectedStringWithDefaultNotNullable') => [
            StringPropertiesFake::class,
            'protectedStringWithDefaultNotNullable',
            StringProperty::class,
            true,
            'default',
        ];

        yield self::normalise('publicStringWithDefaultNotNullable') => [
            StringPropertiesFake::class,
            'publicStringWithDefaultNotNullable',
            StringProperty::class,
            true,
            'default',
        ];

        yield self::normalise('privateStringWithoutDefaultNullable') => [
            StringPropertiesFake::class,
            'privateStringWithoutDefaultNullable',
            StringProperty::class,
            false,
        ];

        yield self::normalise('protectedStringWithoutDefaultNullable') => [
            StringPropertiesFake::class,
            'protectedStringWithoutDefaultNullable',
            StringProperty::class,
            false,
        ];

        yield self::normalise('publicStringWithoutDefaultNullable') => [
            StringPropertiesFake::class,
            'publicStringWithoutDefaultNullable',
            StringProperty::class,
            false,
        ];

        yield self::normalise('privateStringWithDefaultNullable') => [
            StringPropertiesFake::class,
            'privateStringWithDefaultNullable',
            StringProperty::class,
            true,
            null,
        ];

        yield self::normalise('protectedStringWithDefaultNullable') => [
            StringPropertiesFake::class,
            'protectedStringWithDefaultNullable',
            StringProperty::class,
            true,
            null,
        ];

        yield self::normalise('publicStringWithDefaultNullable') => [
            StringPropertiesFake::class,
            'publicStringWithDefaultNullable',
            StringProperty::class,
            true,
            null,
        ];

        yield self::normalise('privateUntypedStringWithDefaultNotNullable') => [
            StringPropertiesFake::class,
            'privateUntypedStringWithDefaultNotNullable',
            MixedProperty::class,
            true,
            'default',
        ];

        yield self::normalise('protectedUntypedStringWithDefaultNotNullable') => [
            StringPropertiesFake::class,
            'protectedUntypedStringWithDefaultNotNullable',
            MixedProperty::class,
            true,
            'default',
        ];

        yield self::normalise('publicUntypedStringWithDefaultNotNullable') => [
            StringPropertiesFake::class,
            'publicUntypedStringWithDefaultNotNullable',
            MixedProperty::class,
            true,
            'default',
        ];

        yield self::normalise('stringWithAttrRegex') => [
            StringPropertiesFake::class,
            'stringWithAttrRegex',
            StringProperty::class,
            false,
        ];

        yield self::normalise('stringWithAttrUuid') => [
            StringPropertiesFake::class,
            'stringWithAttrUuid',
            StringProperty::class,
            false,
        ];

        yield self::normalise('stringWithAttrUuidAndRegex') => [
            StringPropertiesFake::class,
            'stringWithAttrUuidAndRegex',
            StringProperty::class,
            false,
        ];

        yield self::normalise('stringWithAttrEmail') => [
            StringPropertiesFake::class,
            'stringWithAttrEmail',
            StringProperty::class,
            false,
        ];

        yield self::normalise('stringWithAttrEmailAndRegex') => [
            StringPropertiesFake::class,
            'stringWithAttrEmailAndRegex',
            StringProperty::class,
            false,
        ];
    }

    public static function provideIntegerFakesPropertiesAndDefaults(): \Generator
    {
        yield self::normalise('privateIntegerWithoutDefaultNotNullable') => [
            IntegerPropertiesFake::class,
            'privateIntegerWithoutDefaultNotNullable',
            IntegerProperty::class,
            false,
        ];

        yield self::normalise('protectedIntegerWithoutDefaultNotNullable') => [
            IntegerPropertiesFake::class,
            'protectedIntegerWithoutDefaultNotNullable',
            IntegerProperty::class,
            false,
        ];

        yield self::normalise('publicIntegerWithoutDefaultNotNullable') => [
            IntegerPropertiesFake::class,
            'protectedIntegerWithoutDefaultNotNullable',
            IntegerProperty::class,
            false,
        ];

        yield self::normalise('privateIntegerWithDefaultNotNullable') => [
            IntegerPropertiesFake::class,
            'privateIntegerWithDefaultNotNullable',
            IntegerProperty::class,
            true,
            42,
        ];

        yield self::normalise('protectedIntegerWithDefaultNotNullable') => [
            IntegerPropertiesFake::class,
            'protectedIntegerWithDefaultNotNullable',
            IntegerProperty::class,
            true,
            42,
        ];

        yield self::normalise('publicIntegerWithDefaultNotNullable') => [
            IntegerPropertiesFake::class,
            'publicIntegerWithDefaultNotNullable',
            IntegerProperty::class,
            true,
            42,
        ];

        yield self::normalise('privateIntegerWithoutDefaultNullable') => [
            IntegerPropertiesFake::class,
            'privateIntegerWithoutDefaultNullable',
            IntegerProperty::class,
            false,
        ];

        yield self::normalise('protectedIntegerWithoutDefaultNullable') => [
            IntegerPropertiesFake::class,
            'protectedIntegerWithoutDefaultNullable',
            IntegerProperty::class,
            false,
        ];

        yield self::normalise('publicIntegerWithoutDefaultNullable') => [
            IntegerPropertiesFake::class,
            'publicIntegerWithoutDefaultNullable',
            IntegerProperty::class,
            false,
        ];

        yield self::normalise('privateIntegerWithDefaultNullable') => [
            IntegerPropertiesFake::class,
            'privateIntegerWithDefaultNullable',
            IntegerProperty::class,
            true,
            null,
        ];

        yield self::normalise('protectedIntegerWithDefaultNullable') => [
            IntegerPropertiesFake::class,
            'protectedIntegerWithDefaultNullable',
            IntegerProperty::class,
            true,
            null,
        ];

        yield self::normalise('publicIntegerWithDefaultNullable') => [
            IntegerPropertiesFake::class,
            'publicIntegerWithDefaultNullable',
            IntegerProperty::class,
            true,
            null,
        ];

        yield self::normalise('privateUntypedIntegerWithDefaultNotNullable') => [
            IntegerPropertiesFake::class,
            'privateUntypedIntegerWithDefaultNotNullable',
            MixedProperty::class,
            true,
            42,
        ];

        yield self::normalise('protectedUntypedIntegerWithDefaultNotNullable') => [
            IntegerPropertiesFake::class,
            'protectedUntypedIntegerWithDefaultNotNullable',
            MixedProperty::class,
            true,
            42,
        ];

        yield self::normalise('publicUntypedIntegerWithDefaultNotNullable') => [
            IntegerPropertiesFake::class,
            'publicUntypedIntegerWithDefaultNotNullable',
            MixedProperty::class,
            true,
            42,
        ];
    }

    private static function normalise(string $name): string
    {
        return \ucfirst(
            \preg_replace('/(?<=[[:lower:]])(?=[[:upper:]])/u', ' ', $name)
        );
    }
}
