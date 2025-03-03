<?php

declare(strict_types=1);

namespace Hermiod\Tests\Integration\Resource;

use Hermiod\Resource\Property\MixedProperty;
use Hermiod\Resource\Property\StringProperty;
use Hermiod\Tests\Integration\Fakes\StringPropertiesFake;

trait ProvideStringFakesPropertiesAndDefaults
{
    use NormalisePropertyToTestName;

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
}
