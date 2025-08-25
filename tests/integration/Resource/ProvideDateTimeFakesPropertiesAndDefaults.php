<?php

declare(strict_types=1);

namespace Hermiod\Tests\Integration\Resource;

use Hermiod\Resource\Property\DateTimeInterfaceProperty;
use Hermiod\Tests\Integration\Fakes\DateTimePropertiesFake;

trait ProvideDateTimeFakesPropertiesAndDefaults
{
    use NormalisePropertyToTestName;

    public static function provideDateTimeFakesPropertiesAndDefaults(): \Generator
    {
        $fake = DateTimePropertiesFake::class;
        $property = DateTimeInterfaceProperty::class;

        yield self::normalise('privateDateTimeWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'privateDateTimeWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('protectedDateTimeWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedDateTimeWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('publicDateTimeWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedDateTimeWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('privateDateTimeWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'privateDateTimeWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('protectedDateTimeWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'protectedDateTimeWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('publicDateTimeWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'publicDateTimeWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('privateDateTimeWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'privateDateTimeWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];

        yield self::normalise('protectedDateTimeWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'protectedDateTimeWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];

        yield self::normalise('publicDateTimeWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'publicDateTimeWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];
    }
}