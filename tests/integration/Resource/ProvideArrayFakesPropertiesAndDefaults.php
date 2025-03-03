<?php

declare(strict_types=1);

namespace Hermiod\Tests\Integration\Resource;

use Hermiod\Resource\Property\ArrayProperty;
use Hermiod\Resource\Property\MixedProperty;
use Hermiod\Tests\Integration\Fakes\ArrayPropertiesFake;

trait ProvideArrayFakesPropertiesAndDefaults
{
    use NormalisePropertyToTestName;

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
}