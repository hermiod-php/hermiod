<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\MixedProperty;
use Hermiod\Resource\Property\Traits\ConvertToSameJsonValue;
use Hermiod\Resource\Property\Traits\GetPropertyNameTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MixedProperty::class)]
#[CoversClass(GetPropertyNameTrait::class)]
#[CoversClass(ConvertToSameJsonValue::class)]
class MixedPropertyTest extends TestCase
{
    public function testImplementsPropertyInterface(): void
    {
        $this->assertInstanceOf(
            PropertyInterface::class,
            new MixedProperty('foo', false),
        );
    }

    public function testGetPropertyName(): void
    {
        $property = new MixedProperty('foo');

        $this->assertSame('foo', $property->getPropertyName());
    }

    public function testGetDefaultValueWhenNoneSet(): void
    {
        $property = new MixedProperty('foo');

        $this->assertFalse($property->hasDefaultValue());;
        $this->assertNull($property->getDefaultValue());
    }

    #[DataProvider('jsonValueProvider')]
    public function testNormalisingToJsonReturnsSameValue(mixed $value): void
    {
        $property = new MixedProperty('foo');

        $this->assertSame($value, $property->normaliseJsonValue($value));
    }

    public static function jsonValueProvider(): array
    {
        return [
            'string' => ['foo'],
            'int' => [123],
            'float' => [12.34],
            'array' => [[]],
            'object' => [(object) ['prop' => 'value']],
            'true' => [true],
            'false' => [false],
            'null' => [null],
        ];
    }

    #[DataProvider('typesProvider')]
    public function testGetDefaultValueWhenSet(mixed $default): void
    {
        $property = MixedProperty::withDefaultValue('foo', $default);

        $this->assertTrue($property->hasDefaultValue());
        $this->assertSame($default, $property->getDefaultValue());
    }

    #[DataProvider('typesProvider')]
    public function testAllValuesAreValidAgainstConstraints(mixed $value): void
    {
        $property = new MixedProperty('foo');
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('typesProvider')]
    public function testAllValuesNormaliseToThemselves(mixed $value): void
    {
        $property = new MixedProperty('foo');

        $this->assertSame(
            $value,
            $property->normalisePhpValue($value),
        );
    }

    public function testNullValuesNormaliseToDefault(): void
    {
        $property = MixedProperty::withDefaultValue('foo', 'bar');

        $this->assertSame(
            'bar',
            $property->normalisePhpValue(null),
        );
    }

    public static function typesProvider(): \Generator
    {
        $types = [
            0,
            1,
            -1,
            2.3,
            -85.25,
            '0',
            '1',
            '-1',
            '2.3',
            '-85.25',
            ' ',
            '',
            [],
            (object)[],
            true,
            false,
            null,
        ];

        foreach ($types as $value) {
            $key = \sprintf(
                '%s %s',
                \strtolower(
                    \gettype($value)
                ),
                \json_encode($value),
            );

            yield $key => [$value];
        }
    }
}
