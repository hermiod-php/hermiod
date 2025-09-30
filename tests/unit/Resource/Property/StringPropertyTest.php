<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property;

use Hermiod\Attribute\Constraint\StringConstraintInterface;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\Exception\InvalidDefaultValueException;
use Hermiod\Resource\Property\Exception\InvalidPropertyNameException;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\StringProperty;
use Hermiod\Resource\Property\Traits\ConstructWithNameAndNullableTrait;
use Hermiod\Resource\Property\Traits\ConvertToSameJsonValue;
use Hermiod\Resource\Property\Traits\GetPropertyNameTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringProperty::class)]
#[CoversClass(ConstructWithNameAndNullableTrait::class)]
#[CoversClass(GetPropertyNameTrait::class)]
#[CoversClass(ConvertToSameJsonValue::class)]
class StringPropertyTest extends TestCase
{
    use InvalidPhpPropertyNameProviderTrait;

    public function testImplementsPropertyInterface(): void
    {
        $this->assertInstanceOf(
            PropertyInterface::class,
            new StringProperty('foo', false),
        );
    }

    public function testGetPropertyName(): void
    {
        $property = new StringProperty('foo', false);

        $this->assertSame('foo', $property->getPropertyName());
    }

    public function testGetDefaultValueWhenNoneSet(): void
    {
        $property = new StringProperty('foo', false);

        $this->assertFalse($property->hasDefaultValue());;
        $this->assertNull($property->getDefaultValue());
    }

    public function testGetDefaultValueWhenSet(): void
    {
        $property = StringProperty::withDefaultValue('foo', false, 'bar');

        $this->assertTrue($property->hasDefaultValue());;
        $this->assertSame('bar', $property->getDefaultValue());
    }

    public function testCannotSetDefaultValueToNullWhenNotNullable(): void
    {
        $this->expectException(InvalidDefaultValueException::class);

        StringProperty::withDefaultValue('foo', false, null);
    }

    public function testAddingConstraints(): void
    {
        $property = StringProperty::withDefaultValue('foo', false, 'bar');

        $constraint = $this->createMock(StringConstraintInterface::class);

        $new = $property->withConstraint($constraint);

        $this->assertNotSame($property, $new);
    }

    #[DataProvider('validValueProvider')]
    public function testNonNullablePropertyValidatesStringsWithoutConstraints(string $value): void
    {
        $property = new StringProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('invalidValueProvider')]
    public function testNonNullablePropertyDoesNotValidateNonStringsWithoutConstraints(mixed $value): void
    {
        $property = new StringProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    public function testNonNullablePropertyDoesNotValidateNullWithoutConstraints(): void
    {
        $property = new StringProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, null)->isValid()
        );
    }

    #[DataProvider('validValueProvider')]
    public function testNullablePropertyValidatesStringsWithoutConstraints(string $value): void
    {
        $property = new StringProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('invalidValueProvider')]
    public function testNullablePropertyDoesNotValidateNonStringsWithoutConstraints(mixed $value): void
    {
        $property = new StringProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    public function testNullablePropertyValidatesNullWithoutConstraints(): void
    {
        $property = new StringProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, null)->isValid()
        );
    }

    #[DataProvider('validValueProvider')]
    public function testNonNullablePropertyEnforcesInvalidatingConstraints(string $value): void
    {
        $property = new StringProperty('foo', false);

        $path = $this->createMock(PathInterface::class);
        $constraint = $this->createMock(StringConstraintInterface::class);

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
            ->willReturnCallback(function (PathInterface $path, string $value) {
                return \sprintf('%s was tested with value %s', $path->__toString(), $value);
            });

        $property = $property->withConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        $this->assertFalse($result->isValid());
        $this->assertSame('$.test was tested with value ' . $value, $result->getValidationErrors()[0]);
    }

    #[DataProvider('validValueProvider')]
    public function testNullablePropertyEnforcesInvalidatingConstraints(string $value): void
    {
        $property = new StringProperty('foo', true);

        $path = $this->createMock(PathInterface::class);
        $constraint = $this->createMock(StringConstraintInterface::class);

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
            ->willReturnCallback(function (PathInterface $path, string $value) {
                return \sprintf('%s was tested with value %s', $path->__toString(), $value);
            });

        $property = $property->withConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        $this->assertFalse($result->isValid());
        $this->assertSame('$.test was tested with value ' . $value, $result->getValidationErrors()[0]);
    }

    #[DataProvider('validValueProvider')]
    public function testNonNullablePropertyEnforcesValidatingConstraints(string $value): void
    {
        $property = new StringProperty('foo', false);

        $path = $this->createMock(PathInterface::class);
        $constraint = $this->createMock(StringConstraintInterface::class);

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
    public function testNullablePropertyEnforcesValidatingConstraints(string $value): void
    {
        $property = new StringProperty('foo', true);

        $path = $this->createMock(PathInterface::class);
        $constraint = $this->createMock(StringConstraintInterface::class);

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
        $property = new StringProperty('foo', false);

        $this->assertSame($expected, $property->normalisePhpValue($value));
    }

    #[DataProvider('normalisationCasesWithDefault')]
    public function testNormalisingToPhpValueWithDefault(string|null $default, mixed $value, mixed $expected): void
    {
        $property = StringProperty::withDefaultValue('foo', true, $default);

        $this->assertSame($expected, $property->normalisePhpValue($value));
    }

    #[DataProvider('jsonValueProvider')]
    public function testNormalisingToJsonReturnsSameValue(mixed $value): void
    {
        $property = StringProperty::withDefaultValue('foo', true, null);

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

    public static function normalisationCasesWithoutDefault(): array
    {
        return [
            'string' => ['foo', 'foo'],
            'int' => [123, '123'],
            'float' => [12.34, '12.34'],
            'array' => [[], ''],
            'object' => [(object) ['prop' => 'value'], ''],
            'true' => [true, 'true'],
            'false' => [false, 'false'],
            'null' => [null, ''],
        ];
    }

    public static function normalisationCasesWithDefault(): array
    {
        return [
            'string' => ['default', 'foo', 'foo'],
            'int' => ['default', 123, '123'],
            'float' => ['default', 12.34, '12.34'],
            'array to string' => ['default', [], 'default'],
            'array to null' => [null, [], null],
            'object to string' => ['default', (object) ['prop' => 'value'], 'default'],
            'object to null' => [null, (object) ['prop' => 'value'], null],
            'true' => ['default', true, 'true'],
            'false' => ['default', false, 'false'],
            'null' => ['default', null, null],
        ];
    }

    public static function validValueProvider(): \Generator
    {
        foreach (['', ' ', "\t", '0', 'foo', ' foo '] as $value) {
            yield $value => [$value];
        }

        yield 'massive string' => [\str_repeat('abcdefghijklmnopqurstuvwxyz0123456789', 100)];
    }

    public static function invalidValueProvider(): \Generator
    {
        foreach ([0, 1, -1, 2.3, -85.25, [], (object)[], false, true] as $value) {
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

        new StringProperty($name, false);
    }
}
