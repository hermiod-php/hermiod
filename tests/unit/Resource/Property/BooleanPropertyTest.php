<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\Exception\InvalidDefaultValueException;
use Hermiod\Resource\Property\Exception\InvalidPropertyNameException;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\BooleanProperty;
use Hermiod\Resource\Property\Traits\ConstructWithNameAndNullableTrait;
use Hermiod\Resource\Property\Traits\GetPropertyNameTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(BooleanProperty::class)]
#[CoversClass(ConstructWithNameAndNullableTrait::class)]
#[CoversClass(GetPropertyNameTrait::class)]
class BooleanPropertyTest extends TestCase
{
    use InvalidPhpPropertyNameProviderTrait;

    public function testImplementsPropertyInterface(): void
    {
        $this->assertInstanceOf(
            PropertyInterface::class,
            new BooleanProperty('foo', false),
        );
    }

    public function testGetPropertyName(): void
    {
        $property = new BooleanProperty('foo', false);

        $this->assertSame('foo', $property->getPropertyName());
    }

    public function testGetDefaultValueWhenNoneSet(): void
    {
        $property = new BooleanProperty('foo', false);

        $this->assertFalse($property->hasDefaultValue());;
        $this->assertNull($property->getDefaultValue());
    }

    public function testGetDefaultValueWhenSet(): void
    {
        $property = BooleanProperty::withDefaultValue('foo', false, false);

        $this->assertTrue($property->hasDefaultValue());
        $this->assertFalse($property->getDefaultValue());

        $property = BooleanProperty::withDefaultValue('foo', false, true);

        $this->assertTrue($property->hasDefaultValue());
        $this->assertTrue($property->getDefaultValue());

        $property = BooleanProperty::withDefaultValue('foo', true, null);

        $this->assertTrue($property->hasDefaultValue());
        $this->assertNull($property->getDefaultValue());
    }

    public function testCannotSetDefaultValueToNullWhenNotNullable(): void
    {
        $this->expectException(InvalidDefaultValueException::class);

        BooleanProperty::withDefaultValue('foo', false, null);
    }

    #[DataProvider('jsonValueProvider')]
    public function testNormalisingToJsonReturnsSameValue(mixed $value): void
    {
        $property = BooleanProperty::withDefaultValue('foo', false, true);

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

    #[TestWith([true])]
    #[TestWith([false])]
    public function testNonNullablePropertyValidatesBooleans(bool $value): void
    {
        $property = new BooleanProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('invalidValueProvider')]
    public function testNonNullablePropertyDoesNotValidateNonBooleans(mixed $value): void
    {
        $property = new BooleanProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    public function testNonNullablePropertyDoesNotValidateNull(): void
    {
        $property = new BooleanProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, null)->isValid()
        );
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function testNullablePropertyValidatesBooleans(bool $value): void
    {
        $property = new BooleanProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('invalidValueProvider')]
    public function testNullablePropertyDoesNotValidateNonBooleans(mixed $value): void
    {
        $property = new BooleanProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    public function testNullablePropertyValidatesNull(): void
    {
        $property = new BooleanProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, null)->isValid()
        );
    }

    #[DataProvider('normalisationCasesWithoutDefault')]
    public function testNormalisingToPhpValueWithoutDefault(mixed $value, mixed $expected): void
    {
        $property = new BooleanProperty('foo', false);

        $this->assertSame($expected, $property->normalisePhpValue($value));
    }

    #[TestWith([false, null, null], 'Null wih false default')]
    #[TestWith([true, null, null], 'Null wih true default')]
    #[TestWith([null, null, null], 'Null wih null default')]
    #[TestWith([null, true, true], 'True wih null default')]
    #[TestWith([null, false, false], 'False wih null default')]
    public function testNormalisingNullableToPhpValueWithDefault(bool|null $default, bool|null $value, bool|null $expected): void
    {
        $property = BooleanProperty::withDefaultValue('foo', true, $default);

        $this->assertSame($expected, $property->normalisePhpValue($value));
    }

    #[TestWith([false, null, false], 'Null wih false default')]
    #[TestWith([true, null, true], 'Null wih true default')]
    public function testNormalisingNonNullableToPhpValueWithDefault(bool|null $default, bool|null $value, bool|null $expected): void
    {
        $property = BooleanProperty::withDefaultValue('foo', false, $default);

        $this->assertSame($expected, $property->normalisePhpValue($value));
    }

    public static function normalisationCasesWithoutDefault(): array
    {
        return [
            'int 1' => [1, true],
            'int 0' => [0, false],
            'bool 1.0' => [1.0, true],
            'bool 0.0' => [0.0, false],
            'string "1"' => ['1', true],
            'string "0"' => ['0', false],
            'array' => [[], false],
            'object' => [(object) ['prop' => 'value'], false],
            'true' => [true, true],
            'false' => [false, false],
            'null' => [null, false],
        ];
    }

    public static function invalidValueProvider(): \Generator
    {
        foreach ([0, 1, -1, 2.3, -85.25, '0', '1', '-1', '2.3', '-85.25', ' ', '', [], (object)[]] as $value) {
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

    #[DataProvider('invalidPhpPropertyNameProvider')]
    public function testConstructorThrowsExceptionForInvalidPropertyName(string $name): void
    {
        $this->expectException(InvalidPropertyNameException::class);

        new BooleanProperty($name, false);
    }
}
