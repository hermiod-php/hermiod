<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property;

use Hermiod\Attribute\Constraint\ArrayConstraintInterface;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Path\Root;
use Hermiod\Resource\Property\Exception\InvalidDefaultValueException;
use Hermiod\Resource\Property\Exception\InvalidPropertyNameException;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\ArrayProperty;
use Hermiod\Resource\Property\Traits\ConstructWithNameAndNullableTrait;
use Hermiod\Resource\Property\Traits\ConvertToSameJsonValue;
use Hermiod\Resource\Property\Traits\GetPropertyNameTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayProperty::class)]
#[CoversClass(ConstructWithNameAndNullableTrait::class)]
#[CoversClass(GetPropertyNameTrait::class)]
#[CoversClass(ConvertToSameJsonValue::class)]
class ArrayPropertyTest extends TestCase
{
    use InvalidPhpPropertyNameProviderTrait;

    public function testImplementsPropertyInterface(): void
    {
        $this->assertInstanceOf(
            PropertyInterface::class,
            new ArrayProperty('foo', false),
        );
    }

    public function testGetPropertyName(): void
    {
        $property = new ArrayProperty('foo', false);

        $this->assertSame('foo', $property->getPropertyName());
    }

    public function testIsNullableWhenTrue(): void
    {
        $property = new ArrayProperty('foo', true);

        $this->assertTrue($property->isNullable());
    }

    public function testIsNullableWhenFalse(): void
    {
        $property = new ArrayProperty('foo', false);

        $this->assertFalse($property->isNullable());
    }

    public function testGetDefaultValueWhenNoneSet(): void
    {
        $property = new ArrayProperty('foo', false);

        $this->assertFalse($property->hasDefaultValue());;
        $this->assertNull($property->getDefaultValue());
    }

    public function testGetDefaultValueWhenSet(): void
    {
        $property = ArrayProperty::withDefaultValue('foo', false, ['foo', 'bar', 'baz']);

        $this->assertTrue($property->hasDefaultValue());
        $this->assertIsArray($property->getDefaultValue());
        $this->assertSame(['foo', 'bar', 'baz'], $property->getDefaultValue());
    }

    public function testCannotSetDefaultValueToNullWhenNotNullable(): void
    {
        $this->expectException(InvalidDefaultValueException::class);

        ArrayProperty::withDefaultValue('foo', false, null);
    }

    public function testAddingConstraints(): void
    {
        $property = ArrayProperty::withDefaultValue('foo', false, []);

        $constraint = $this->createMock(ArrayConstraintInterface::class);

        $new = $property->withConstraint($constraint);

        $this->assertNotSame($property, $new);
    }

    #[DataProvider('jsonValueProvider')]
    public function testNormalisingToJsonReturnsSameValue(mixed $value): void
    {
        $property = ArrayProperty::withDefaultValue('foo', false, []);

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
    public function testNonNullablePropertyValidatesArraysWithoutConstraints(array $value): void
    {
        $property = new ArrayProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('invalidValueProvider')]
    public function testNonNullablePropertyDoesNotValidateNonArrayObjectWithoutConstraints(mixed $value): void
    {
        $property = new ArrayProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    public function testNonNullablePropertyDoesNotValidateNullWithoutConstraints(): void
    {
        $property = new ArrayProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, null)->isValid()
        );
    }

    #[DataProvider('validValueProvider')]
    public function testNullablePropertyValidatesArraysWithoutConstraints(array $value): void
    {
        $property = new ArrayProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('invalidValueProvider')]
    public function testNullablePropertyDoesNotValidateNonArrayObjectWithoutConstraints(mixed $value): void
    {
        $property = new ArrayProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    public function testNullablePropertyValidatesNullWithoutConstraints(): void
    {
        $property = new ArrayProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, null)->isValid()
        );
    }

    #[DataProvider('validValueProvider')]
    public function testNonNullablePropertyEnforcesInvalidatingConstraints(array $value): void
    {
        $property = new ArrayProperty('foo', false);

        $path = $this->createPathMock();
        $constraint = $this->createMock(ArrayConstraintInterface::class);

        $path
            ->expects($this->once())
            ->method('__toString')
            ->willReturn('$.test');

        $constraint
            ->expects($this->once())
            ->method('mapValueMatchesConstraint')
            ->willReturn(false);

        $constraint
            ->expects($this->once())
            ->method('getMismatchExplanation')
            ->willReturnCallback(function (PathInterface $path, string|int $value) {
                return \sprintf('%s was tested with value %s', $path->__toString(), $value);
            });

        $property = $property->withConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        $this->assertFalse($result->isValid());

        $this->assertSame(
            \sprintf('$.test was tested with value %s', \current($value)),
            $result->getValidationErrors()[0]
        );
    }

    #[DataProvider('validValueProvider')]
    public function testNullablePropertyEnforcesInvalidatingConstraints(array $value): void
    {
        $property = new ArrayProperty('foo', true);

        $path = $this->createPathMock();
        $constraint = $this->createMock(ArrayConstraintInterface::class);

        $path
            ->expects($this->once())
            ->method('__toString')
            ->willReturn('$.test');

        $constraint
            ->expects($this->once())
            ->method('mapValueMatchesConstraint')
            ->willReturn(false);

        $constraint
            ->expects($this->once())
            ->method('getMismatchExplanation')
            ->willReturnCallback(function (PathInterface $path, string|int $value) {
                return \sprintf('%s was tested with value %s', $path->__toString(), \json_encode($value));
            });

        $property = $property->withConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        if (\is_array($value) || \is_object($value)) {
            $value = \current((array) $value);
        }

        $this->assertFalse($result->isValid());
        $this->assertSame(\sprintf('$.test was tested with value %s', \json_encode($value)), $result->getValidationErrors()[0]);
    }

    #[DataProvider('validValueProvider')]
    public function testNonNullablePropertyEnforcesValidatingConstraints(array $value): void
    {
        $property = new ArrayProperty('foo', false);

        $path = $this->createPathMock();
        $constraint = $this->createMock(ArrayConstraintInterface::class);

        $path
            ->expects($this->never())
            ->method('__toString');

        $constraint
            ->expects($this->exactly(2))
            ->method('mapValueMatchesConstraint')
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
    public function testNullablePropertyEnforcesValidatingConstraints(array $value): void
    {
        $property = new ArrayProperty('foo', true);

        $path = $this->createPathMock();
        $constraint = $this->createMock(ArrayConstraintInterface::class);

        $path
            ->expects($this->never())
            ->method('__toString');

        $constraint
            ->expects($this->exactly(2))
            ->method('mapValueMatchesConstraint')
            ->willReturn(true);

        $constraint
            ->expects($this->never())
            ->method('getMismatchExplanation');

        $property = $property->withConstraint($constraint)->withConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        $this->assertTrue($result->isValid());
        $this->assertCount(0, $result->getValidationErrors());
    }

    public function testConstraintErrorsCompoundForAllValues(): void
    {
        $property = new ArrayProperty('foo', false);

        $constraint = $this->createMock(ArrayConstraintInterface::class);

        $map = [
            'one' => '1ne',
            'two' => 2,
            'key' => 'three',
            'array' => [],
        ];

        $list = [
            'first',
            3,
            [],
        ];

        $constraint
            ->expects($this->exactly(\count($list) + \count($map)))
            ->method('mapValueMatchesConstraint')
            ->willReturn(false);

        $constraint
            ->expects($this->exactly(\count($list) + \count($map)))
            ->method('getMismatchExplanation')
            ->willReturnCallback(function (PathInterface $path, mixed $value) {
                return \sprintf('%s was tested with value %s', $path->__toString(), \json_encode($value));
            });

        $property = $property->withConstraint($constraint);

        $path = new Root();

        $result = $property->checkValueAgainstConstraints($path->withObjectKey('foo'), $map);

        $this->assertFalse($result->isValid());

        $this->assertCount(
            4,
            $result->getValidationErrors(),
            \sprintf('Received: %s', \json_encode($result->getValidationErrors(), \JSON_PRETTY_PRINT)),
        );

        $this->assertSame('$.foo.one was tested with value "1ne"', $result->getValidationErrors()[0]);
        $this->assertSame('$.foo.two was tested with value 2', $result->getValidationErrors()[1]);
        $this->assertSame('$.foo.key was tested with value "three"', $result->getValidationErrors()[2]);
        $this->assertSame('$.foo.array was tested with value []', $result->getValidationErrors()[3]);

        $result = $property->checkValueAgainstConstraints($path->withObjectKey('foo'), $list);

        $this->assertFalse($result->isValid());

        $this->assertCount(
            3,
            $result->getValidationErrors(),
            \sprintf('Received: %s', \json_encode($result->getValidationErrors(), \JSON_PRETTY_PRINT)),
        );

        $this->assertSame('$.foo[0] was tested with value "first"', $result->getValidationErrors()[0]);
        $this->assertSame('$.foo[1] was tested with value 3', $result->getValidationErrors()[1]);
        $this->assertSame('$.foo[2] was tested with value []', $result->getValidationErrors()[2]);
    }

    #[DataProvider('normalisationCasesWithoutDefault')]
    public function testNormalisingToPhpValueWithoutDefault(mixed $value, mixed $expected): void
    {
        $property = new ArrayProperty('foo', false);

        $this->assertSame($expected, $property->normalisePhpValue($value));
    }

    #[DataProvider('normalisationCasesWithDefault')]
    public function testNormalisingToPhpValueWithDefault(array|null $default, mixed $value, mixed $expected): void
    {
        $property = ArrayProperty::withDefaultValue('foo', true, $default);

        $this->assertSame($expected, $property->normalisePhpValue($value));
    }

    #[DataProvider('invalidPhpPropertyNameProvider')]
    public function testConstructorThrowsExceptionForInvalidPropertyName(string $name): void
    {
        $this->expectException(InvalidPropertyNameException::class);

        new ArrayProperty($name, false);
    }

    public static function normalisationCasesWithoutDefault(): array
    {
        return [
            'int' => [123, []],
            'float' => [123.00012, []],
            'int string' => ['123', []],
            'float string' => ['12.34', []],
            'list[]' => [['foo', 'bar'], ['foo', 'bar']],
            'hash[]' => [['foo' => 'bar'], ['foo' =>'bar']],
            'object' => [(object) ['prop' => 'value'], ['prop' => 'value']],
            'true' => [true, []],
            'false' => [false, []],
            'null' => [null, []],
        ];
    }

    public static function normalisationCasesWithDefault(): array
    {
        return [
            'string' => [['default'], 'foo', ['default']],
            'float' => [['default'], 12.34, ['default']],
            'array to int' => [['default'], ['foo', 'bar'], ['foo', 'bar']],
            'object to int' => [['default'], (object) ['prop' => 'value'], ['prop' => 'value']],
            'true' => [['default'], true, ['default']],
            'false' => [['default'], false, ['default']],
            'null' => [['default'], null, ['default']],
        ];
    }

    public static function validValueProvider(): \Generator
    {
        $cases = [
            ['default'],
            ['default' => 'value'],
        ];

        foreach ($cases as $value) {
            yield \print_r($value, true) => [$value];
        }
    }

    public static function invalidValueProvider(): \Generator
    {
        foreach (['0', '1', '-1', '2.3', '-85.25', ' ', '', 1, -1, 1.1, -1.1, (object)[], false, true] as $value) {
            $key = \sprintf(
                '%s %s',
                \get_debug_type($value),
                \json_encode($value),
            );

            yield $key => [$value];
        }
    }

    public function createPathMock(): PathInterface & MockObject
    {
        $path = $this->createMock(PathInterface::class);

        $path
            ->method('withArrayKey')
            ->willReturn($path);

        $path
            ->method('withObjectKey')
            ->willReturn($path);

        return $path;
    }
}
