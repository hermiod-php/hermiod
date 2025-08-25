<?php

declare(strict_types=1);

namespace Hermiod\Tests\Integration\Resource;

use Hermiod\Resource\Property\DateTimeInterfaceProperty;
use Hermiod\Tests\Integration\Fakes\DateTimeImmutablePropertiesFake;

trait ProvideDateTimeImmutableFakesPropertiesAndDefaults
{
    use NormalisePropertyToTestName;

    public static function provideDateTimeImmutableFakesPropertiesAndDefaults(): \Generator
    {
        $fake = DateTimeImmutablePropertiesFake::class;
        $property = DateTimeInterfaceProperty::class;

        yield self::normalise('privateDateTimeImmutableWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'privateDateTimeImmutableWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('protectedDateTimeImmutableWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedDateTimeImmutableWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('publicDateTimeImmutableWithoutDefaultNotNullable') => [
            'fake' => $fake,
            'name' => 'protectedDateTimeImmutableWithoutDefaultNotNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('privateDateTimeImmutableWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'privateDateTimeImmutableWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('protectedDateTimeImmutableWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'protectedDateTimeImmutableWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('publicDateTimeImmutableWithoutDefaultNullable') => [
            'fake' => $fake,
            'name' => 'publicDateTimeImmutableWithoutDefaultNullable',
            'class' => $property,
            'expectDefault' => false,
        ];

        yield self::normalise('privateDateTimeImmutableWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'privateDateTimeImmutableWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];

        yield self::normalise('protectedDateTimeImmutableWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'protectedDateTimeImmutableWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];

        yield self::normalise('publicDateTimeImmutableWithDefaultNullable') => [
            'fake' => $fake,
            'name' => 'publicDateTimeImmutableWithDefaultNullable',
            'class' => $property,
            'expectDefault' => true,
            'default' => null,
        ];
    }
}