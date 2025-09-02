<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property;

use Hermiod\Attribute\Constraint\NumberConstraintInterface;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\Exception\InvalidDefaultValueException;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\FloatProperty;
use Hermiod\Resource\Property\Traits\ConstructWithNameAndNullableTrait;
use Hermiod\Resource\Property\Traits\ConvertToSameJsonValue;
use Hermiod\Resource\Property\Traits\GetPropertyNameTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(FloatProperty::class)]
#[CoversClass(ConstructWithNameAndNullableTrait::class)]
#[CoversClass(GetPropertyNameTrait::class)]
#[CoversClass(ConvertToSameJsonValue::class)]
class FloatPropertyTest extends TestCase
{
    public function testImplementsPropertyInterface(): void
    {
        $this->assertInstanceOf(
            PropertyInterface::class,
            new FloatProperty('foo', false),
        );
    }

    public function testGetPropertyName(): void
    {
        $property = new FloatProperty('foo', false);

        $this->assertSame('foo', $property->getPropertyName());
    }

    public function testGetDefaultValueWhenNoneSet(): void
    {
        $property = new FloatProperty('foo', false);

        $this->assertFalse($property->hasDefaultValue());;
        $this->assertNull($property->getDefaultValue());
    }

    public function testGetDefaultValueWhenSet(): void
    {
        $property = FloatProperty::withDefaultValue('foo', false, 42.42);

        $this->assertTrue($property->hasDefaultValue());
        $this->assertSame(42.42, $property->getDefaultValue());
    }

    public function testCannotSetDefaultValueToNullWhenNotNullable(): void
    {
        $this->expectException(InvalidDefaultValueException::class);

        FloatProperty::withDefaultValue('foo', false, null);
    }

    public function testAddingConstraints(): void
    {
        $property = FloatProperty::withDefaultValue('foo', false, 666);

        $constraint = $this->createMock(NumberConstraintInterface::class);

        $new = $property->withConstraint($constraint);

        $this->assertNotSame($property, $new);
    }

    #[DataProvider('jsonValueProvider')]
    public function testNormalisingToJsonReturnsSameValue(mixed $value): void
    {
        $property = FloatProperty::withDefaultValue('foo', false, 666);

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

    #[DataProvider('validValueProvider')]
    public function testNonNullablePropertyValidatesFloatsWithoutConstraints(float $value): void
    {
        $property = new FloatProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('invalidValueProvider')]
    public function testNonNullablePropertyDoesNotValidateNonFloatsWithoutConstraints(mixed $value): void
    {
        $property = new FloatProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    public function testNonNullablePropertyDoesNotValidateNullWithoutConstraints(): void
    {
        $property = new FloatProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, null)->isValid()
        );
    }

    #[DataProvider('validValueProvider')]
    public function testNullablePropertyValidatesFloatsWithoutConstraints(float $value): void
    {
        $property = new FloatProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('invalidValueProvider')]
    public function testNullablePropertyDoesNotValidateNonFloatsWithoutConstraints(mixed $value): void
    {
        $property = new FloatProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    public function testNullablePropertyValidatesNullWithoutConstraints(): void
    {
        $property = new FloatProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, null)->isValid()
        );
    }

    #[DataProvider('validValueProvider')]
    public function testNonNullablePropertyEnforcesInvalidatingConstraints(float $value): void
    {
        $property = new FloatProperty('foo', false);

        $path = $this->createMock(PathInterface::class);
        $constraint = $this->createMock(NumberConstraintInterface::class);

        $path
            ->expects($this->once())
            ->method('__toString')
            ->willReturn('$.test');

        $constraint
            ->expects($this->once())
            ->method('valueMatchesConstraint')
            ->willReturn(false);

        $constraint
            ->expects($this->once())
            ->method('getMismatchExplanation')
            ->willReturnCallback(function (PathInterface $path, float $value) {
                return \sprintf('%s was tested with value %s', $path->__toString(), $value);
            });

        $property = $property->withConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        $this->assertFalse($result->isValid());
        $this->assertSame('$.test was tested with value ' . $value, $result->getValidationErrors()[0]);
    }

    #[DataProvider('validValueProvider')]
    public function testNullablePropertyEnforcesInvalidatingConstraints(float $value): void
    {
        $property = new FloatProperty('foo', true);

        $path = $this->createMock(PathInterface::class);
        $constraint = $this->createMock(NumberConstraintInterface::class);

        $path
            ->expects($this->once())
            ->method('__toString')
            ->willReturn('$.test');

        $constraint
            ->expects($this->once())
            ->method('valueMatchesConstraint')
            ->willReturn(false);

        $constraint
            ->expects($this->once())
            ->method('getMismatchExplanation')
            ->willReturnCallback(function (PathInterface $path, float $value) {
                return \sprintf('%s was tested with value %s', $path->__toString(), $value);
            });

        $property = $property->withConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        $this->assertFalse($result->isValid());
        $this->assertSame('$.test was tested with value ' . $value, $result->getValidationErrors()[0]);
    }

    #[DataProvider('validValueProvider')]
    public function testNonNullablePropertyEnforcesValidatingConstraints(float $value): void
    {
        $property = new FloatProperty('foo', false);

        $path = $this->createMock(PathInterface::class);
        $constraint = $this->createMock(NumberConstraintInterface::class);

        $path
            ->expects($this->never())
            ->method('__toString');

        $constraint
            ->expects($this->exactly(2))
            ->method('valueMatchesConstraint')
            ->willReturn(true);

        $constraint
            ->expects($this->never())
            ->method('getMismatchExplanation');

        $property = $property->withConstraint($constraint)->withConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        $this->assertTrue($result->isValid());
        $this->assertCount(0, $result->getValidationErrors());
    }

    #[DataProvider('validValueProvider')]
    public function testNullablePropertyEnforcesValidatingConstraints(float $value): void
    {
        $property = new FloatProperty('foo', true);

        $path = $this->createMock(PathInterface::class);
        $constraint = $this->createMock(NumberConstraintInterface::class);

        $path
            ->expects($this->never())
            ->method('__toString');

        $constraint
            ->expects($this->exactly(2))
            ->method('valueMatchesConstraint')
            ->willReturn(true);

        $constraint
            ->expects($this->never())
            ->method('getMismatchExplanation');

        $property = $property->withConstraint($constraint)->withConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        $this->assertTrue($result->isValid());
        $this->assertCount(0, $result->getValidationErrors());
    }

    #[DataProvider('normalisationCasesWithoutDefault')]
    public function testNormalisingToPhpValueWithoutDefault(mixed $value, mixed $expected): void
    {
        $property = new FloatProperty('foo', false);

        $this->assertSame($expected, $property->normalisePhpValue($value));
    }

    #[DataProvider('normalisationCasesWithDefault')]
    public function testNormalisingToPhpValueWithDefault(float|null $default, mixed $value, mixed $expected): void
    {
        $property = FloatProperty::withDefaultValue('foo', true, $default);

        $this->assertSame($expected, $property->normalisePhpValue($value));
    }

    public static function normalisationCasesWithoutDefault(): array
    {
        return [
            'int' => [123, 123.0],
            'float' => [123.00012, 123.00012],
            'int string' => ['123', 123.0],
            'float string' => ['12.34', 12.34],
            'array' => [[], 0.0],
            'object' => [(object) ['prop' => 'value'], 0.0],
            'true' => [true, 1.0],
            'false' => [false, 0.0],
            'null' => [null, 0.0],
        ];
    }

    public static function normalisationCasesWithDefault(): array
    {
        return [
            'int to int' => [99, 'foo', 99.0],
            'int to null' => [null, 'foo', null],
            'float' => [99.69, 12.34, 12.34],
            'array to int' => [99.69, [], 99.69],
            'array to null' => [null, [], null],
            'object to int' => [99.69, (object) ['prop' => 'value'], 99.69],
            'object to null' => [null, (object) ['prop' => 'value'], null],
            'true' => [99.69, true, 1.0],
            'false' => [99.69, false, 0.0],
            'null' => [99.69, null, null],
        ];
    }

    public static function validValueProvider(): \Generator
    {
        foreach ([-1, 0, 1, -1.1, 1.1, \PHP_INT_MAX - 0.01, \PHP_INT_MIN + 0.01] as $value) {
            yield (string) $value => [$value];
        }
    }

    public static function invalidValueProvider(): \Generator
    {
        foreach (['0', '1', '-1', '2.3', '-85.25', ' ', '', [], (object)[], false, true] as $value) {
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
