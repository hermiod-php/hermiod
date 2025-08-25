<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property;

use Hermiod\Attribute\Constraint\NumberConstraintInterface;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\Exception\InvalidDefaultValueException;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\IntegerProperty;
use Hermiod\Resource\Property\Traits\ConstructWithNameAndNullableTrait;
use Hermiod\Resource\Property\Traits\ConvertToSameJsonValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(IntegerProperty::class)]
#[CoversClass(ConstructWithNameAndNullableTrait::class)]
#[CoversClass(ConvertToSameJsonValue::class)]
class IntegerPropertyTest extends TestCase
{
    public function testImplementsPropertyInterface(): void
    {
        $this->assertInstanceOf(
            PropertyInterface::class,
            new IntegerProperty('foo', false),
        );
    }

    public function testGetPropertyName(): void
    {
        $property = new IntegerProperty('foo', false);

        $this->assertSame('foo', $property->getPropertyName());
    }

    public function testGetDefaultValueWhenNoneSet(): void
    {
        $property = new IntegerProperty('foo', false);

        $this->assertFalse($property->hasDefaultValue());;
        $this->assertNull($property->getDefaultValue());
    }

    public function testGetDefaultValueWhenSet(): void
    {
        $property = IntegerProperty::withDefaultValue('foo', false, 42);

        $this->assertTrue($property->hasDefaultValue());
        $this->assertSame(42, $property->getDefaultValue());
    }

    public function testCannotSetDefaultValueToNullWhenNotNullable(): void
    {
        $this->expectException(InvalidDefaultValueException::class);

        IntegerProperty::withDefaultValue('foo', false, null);
    }

    public function testAddingConstraints(): void
    {
        $property = IntegerProperty::withDefaultValue('foo', false, 666);

        $constraint = $this->createMock(NumberConstraintInterface::class);

        $new = $property->withConstraint($constraint);

        $this->assertNotSame($property, $new);
    }

    #[DataProvider('validValueProvider')]
    public function testNonNullablePropertyValidatesIntegersWithoutConstraints(int $value): void
    {
        $property = new IntegerProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('invalidValueProvider')]
    public function testNonNullablePropertyDoesNotValidateNonIntegersWithoutConstraints(mixed $value): void
    {
        $property = new IntegerProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    public function testNonNullablePropertyDoesNotValidateNullWithoutConstraints(): void
    {
        $property = new IntegerProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, null)->isValid()
        );
    }

    #[DataProvider('validValueProvider')]
    public function testNullablePropertyValidatesIntegersWithoutConstraints(int $value): void
    {
        $property = new IntegerProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('invalidValueProvider')]
    public function testNullablePropertyDoesNotValidateNonIntegersWithoutConstraints(mixed $value): void
    {
        $property = new IntegerProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    public function testNullablePropertyValidatesNullWithoutConstraints(): void
    {
        $property = new IntegerProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, null)->isValid()
        );
    }

    #[DataProvider('validValueProvider')]
    public function testNonNullablePropertyEnforcesInvalidatingConstraints(int $value): void
    {
        $property = new IntegerProperty('foo', false);

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
            ->willReturnCallback(function (PathInterface $path, int $value) {
                return \sprintf('%s was tested with value %s', $path->__toString(), $value);
            });

        $property = $property->withConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        $this->assertFalse($result->isValid());
        $this->assertSame('$.test was tested with value ' . $value, $result->getValidationErrors()[0]);
    }

    #[DataProvider('validValueProvider')]
    public function testNullablePropertyEnforcesInvalidatingConstraints(int $value): void
    {
        $property = new IntegerProperty('foo', true);

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
            ->willReturnCallback(function (PathInterface $path, int $value) {
                return \sprintf('%s was tested with value %s', $path->__toString(), $value);
            });

        $property = $property->withConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        $this->assertFalse($result->isValid());
        $this->assertSame('$.test was tested with value ' . $value, $result->getValidationErrors()[0]);
    }

    #[DataProvider('validValueProvider')]
    public function testNonNullablePropertyEnforcesValidatingConstraints(int $value): void
    {
        $property = new IntegerProperty('foo', false);

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
    public function testNullablePropertyEnforcesValidatingConstraints(int $value): void
    {
        $property = new IntegerProperty('foo', true);

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
        $property = new IntegerProperty('foo', false);

        $this->assertSame($expected, $property->normalisePhpValue($value));
    }

    #[DataProvider('normalisationCasesWithDefault')]
    public function testNormalisingToPhpValueWithDefault(int|null $default, mixed $value, mixed $expected): void
    {
        $property = IntegerProperty::withDefaultValue('foo', true, $default);

        $this->assertSame($expected, $property->normalisePhpValue($value));
    }

    public static function normalisationCasesWithoutDefault(): array
    {
        return [
            'int' => [123, 123],
            'float' => [123.00012, 123],
            'int string' => ['123', 123],
            'float string' => ['12.34', 12],
            'array' => [[], 0],
            'object' => [(object) ['prop' => 'value'], 0],
            'true' => [true, 1],
            'false' => [false, 0],
            'null' => [null, 0],
        ];
    }

    public static function normalisationCasesWithDefault(): array
    {
        return [
            'string to int' => [99, 'foo', 99],
            'string to null' => [null, 'foo', null],
            'int' => [99, 123, 123],
            'float' => [99, 12.34, 12],
            'array to int' => [99, [], 99],
            'array to null' => [null, [], null],
            'object to int' => [99, (object) ['prop' => 'value'], 99],
            'object to null' => [null, (object) ['prop' => 'value'], null],
            'true' => [99, true, 1],
            'false' => [99, false, 0],
            'null' => [99, null, null],
        ];
    }

    public static function validValueProvider(): \Generator
    {
        foreach ([-1, 0, 1, \PHP_INT_MAX, \PHP_INT_MIN] as $value) {
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
