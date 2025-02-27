<?php

declare(strict_types=1);

namespace Hermiod\Tests\Integration\Resource;

use Hermiod\Resource\Constraint\CachedFactory;
use Hermiod\Resource\Property\ArrayProperty;
use Hermiod\Resource\Property\CollectionInterface;
use Hermiod\Resource\Property\Factory;
use Hermiod\Resource\Property\IntegerProperty;
use Hermiod\Resource\Property\MixedProperty;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\StringProperty;
use Hermiod\Resource\Resource;
use Hermiod\Tests\Integration\Fakes\ArrayPropertiesFake;
use Hermiod\Tests\Integration\Fakes\IntegerPropertiesFake;
use Hermiod\Tests\Integration\Fakes\StringPropertiesFake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ResourceSuccessTest extends TestCase
{
    #[DataProvider('provideStringFakesPropertiesAndDefaults')]
    #[DataProvider('provideIntegerFakesPropertiesAndDefaults')]
    #[DataProvider('provideArrayFakesPropertiesAndDefaults')]
    public function testCanParseTypedProperties(string $fake, string $name, string $class, bool $expectDefault, mixed $default = null): void
    {
        $reflector = new Resource(
            $fake,
            new Factory(
                new CachedFactory()
            )
        );

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
        $fake = StringPropertiesFake::class;
        $property = StringProperty::class;
        
        yield self::normalise('privateStringWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'privateStringWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('protectedStringWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedStringWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('publicStringWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedStringWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('privateStringWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'privateStringWithDefaultNotNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => 'default',
        ];

        yield self::normalise('protectedStringWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedStringWithDefaultNotNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => 'default',
        ];

        yield self::normalise('publicStringWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'publicStringWithDefaultNotNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => 'default',
        ];

        yield self::normalise('privateStringWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'privateStringWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('protectedStringWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'protectedStringWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('publicStringWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'publicStringWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('privateStringWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'privateStringWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];

        yield self::normalise('protectedStringWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'protectedStringWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];

        yield self::normalise('publicStringWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'publicStringWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];

        yield self::normalise('privateUntypedStringWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'privateUntypedStringWithDefaultNotNullable',
            'class' => MixedProperty::class,
            'expectDefault' => true,
            'default' => 'default',
        ];

        yield self::normalise('protectedUntypedStringWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedUntypedStringWithDefaultNotNullable',
            'class' => MixedProperty::class,
            'expectDefault' => true,
            'default' => 'default',
        ];

        yield self::normalise('publicUntypedStringWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'publicUntypedStringWithDefaultNotNullable',
            'class' => MixedProperty::class,
            'expectDefault' => true,
            'default' => 'default',
        ];

        yield self::normalise('stringWithAttrRegex') => [
            'fake' => $fake,
            'name' => 'stringWithAttrRegex',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('stringWithAttrUuid') => [
            'fake' => $fake,
            'name' => 'stringWithAttrUuid',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('stringWithAttrUuidAndRegex') => [
            'fake' => $fake,
            'name' => 'stringWithAttrUuidAndRegex',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('stringWithAttrEmail') => [
            'fake' => $fake,
            'name' => 'stringWithAttrEmail',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('stringWithAttrEmailAndRegex') => [
            'fake' => $fake,
            'name' => 'stringWithAttrEmailAndRegex',
            'class' => $property,
            'expectDefault' => false,
        ];
    }

    public static function provideIntegerFakesPropertiesAndDefaults(): \Generator
    {
        $fake = IntegerPropertiesFake::class;
        $property = IntegerProperty::class;
        
        yield self::normalise('privateIntegerWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'privateIntegerWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('protectedIntegerWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedIntegerWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('publicIntegerWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedIntegerWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('privateIntegerWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'privateIntegerWithDefaultNotNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => 42,
        ];

        yield self::normalise('protectedIntegerWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedIntegerWithDefaultNotNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => 42,
        ];

        yield self::normalise('publicIntegerWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'publicIntegerWithDefaultNotNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => 42,
        ];

        yield self::normalise('privateIntegerWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'privateIntegerWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('protectedIntegerWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'protectedIntegerWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('publicIntegerWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'publicIntegerWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('privateIntegerWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'privateIntegerWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];

        yield self::normalise('protectedIntegerWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'protectedIntegerWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];

        yield self::normalise('publicIntegerWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'publicIntegerWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];

        yield self::normalise('privateUntypedIntegerWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'privateUntypedIntegerWithDefaultNotNullable',
            'class' => MixedProperty::class,
            'expectDefault' => true,
            'default' => 42,
        ];

        yield self::normalise('protectedUntypedIntegerWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedUntypedIntegerWithDefaultNotNullable',
            'class' => MixedProperty::class,
            'expectDefault' => true,
            'default' => 42,
        ];

        yield self::normalise('publicUntypedIntegerWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'publicUntypedIntegerWithDefaultNotNullable',
            'class' => MixedProperty::class,
            'expectDefault' => true,
            'default' => 42,
        ];
    }

    public static function provideArrayFakesPropertiesAndDefaults(): \Generator
    {
        $fake = ArrayPropertiesFake::class;
        $property = ArrayProperty::class;

        yield self::normalise('privateArrayWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'privateArrayWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('protectedArrayWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedArrayWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('publicArrayWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedArrayWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('privateArrayWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'privateArrayWithDefaultNotNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => ['default'],
        ];

        yield self::normalise('protectedArrayWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedArrayWithDefaultNotNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => ['default'],
        ];

        yield self::normalise('publicArrayWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'publicArrayWithDefaultNotNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => ['default'],
        ];

        yield self::normalise('privateArrayWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'privateArrayWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('protectedArrayWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'protectedArrayWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('publicArrayWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'publicArrayWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('privateArrayWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'privateArrayWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];

        yield self::normalise('protectedArrayWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'protectedArrayWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];

        yield self::normalise('publicArrayWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'publicArrayWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];

        yield self::normalise('privateUntypedArrayWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'privateUntypedArrayWithDefaultNotNullable',
            'class' => MixedProperty::class,
            'expectDefault' => true,
            'default' => ['default'],
        ];

        yield self::normalise('protectedUntypedArrayWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedUntypedArrayWithDefaultNotNullable',
            'class' => MixedProperty::class,
            'expectDefault' => true,
            'default' => ['default'],
        ];

        yield self::normalise('publicUntypedArrayWithDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'publicUntypedArrayWithDefaultNotNullable',
            'class' => MixedProperty::class,
            'expectDefault' => true,
            'default' => ['default'],
        ];
    }

    private static function normalise(string $name): string
    {
        return \ucfirst(
            \preg_replace('/(?<=[[:lower:]])(?=[[:upper:]])/u', ' ', $name)
        );
    }
}
