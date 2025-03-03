<?php

declare(strict_types=1);

namespace Hermiod\Tests\Integration\Resource;

use Hermiod\Resource\Property\IntegerProperty;
use Hermiod\Resource\Property\MixedProperty;
use Hermiod\Tests\Integration\Fakes\IntegerPropertiesFake;

trait ProvideIntegerFakesPropertiesAndDefaults
{
    use NormalisePropertyToTestName;

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
}