<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property;

use Hermiod\Attribute\Constraint\ObjectKeyConstraintInterface;
use Hermiod\Attribute\Constraint\ObjectValueConstraintInterface;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Path\Root;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\ObjectProperty;
use Hermiod\Resource\Property\Traits\ConstructWithNameAndNullableTrait;
use Hermiod\Resource\Property\Traits\ConvertToSameJsonValue;
use Hermiod\Resource\Property\Traits\GetPropertyNameTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectProperty::class)]
#[CoversClass(ConstructWithNameAndNullableTrait::class)]
#[CoversClass(GetPropertyNameTrait::class)]
#[CoversClass(ConvertToSameJsonValue::class)]
class ObjectPropertyTest extends TestCase
{
    public function testImplementsPropertyInterface(): void
    {
        $this->assertInstanceOf(
            PropertyInterface::class,
            new ObjectProperty('foo', false),
        );
    }

    public function testGetPropertyName(): void
    {
        $property = new ObjectProperty('foo', false);

        $this->assertSame('foo', $property->getPropertyName());
    }

    public function testGetDefaultValueWhenNoneSet(): void
    {
        $property = new ObjectProperty('foo', false);

        $this->assertFalse($property->hasDefaultValue());;
        $this->assertNull($property->getDefaultValue());
    }

    public function testAddingKeyConstraints(): void
    {
        $property = new ObjectProperty('foo', false);

        $constraint = $this->createMock(ObjectKeyConstraintInterface::class);

        $new = $property->withKeyConstraint($constraint);

        $this->assertNotSame($property, $new);
    }

    public function testAddingValueConstraints(): void
    {
        $property = new ObjectProperty('foo', false);

        $constraint = $this->createMock(ObjectValueConstraintInterface::class);

        $new = $property->withValueConstraint($constraint);

        $this->assertNotSame($property, $new);
    }

    #[DataProvider('validValueProvider')]
    public function testNonNullablePropertyValidatesArraysWithoutConstraints(array|object|null $value): void
    {
        $property = new ObjectProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('invalidValueProvider')]
    public function testNonNullablePropertyDoesNotValidateNonArrayObjectWithoutConstraints(mixed $value): void
    {
        $property = new ObjectProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    public function testNonNullablePropertyDoesNotValidateNullWithoutConstraints(): void
    {
        $property = new ObjectProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, null)->isValid()
        );
    }

    #[DataProvider('validValueProvider')]
    public function testNullablePropertyValidatesObjectsWithoutConstraints(object|array|null $value): void
    {
        $property = new ObjectProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('invalidValueProvider')]
    public function testNullablePropertyDoesNotValidateNonObjectWithoutConstraints(mixed $value): void
    {
        $property = new ObjectProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    public function testNullablePropertyValidatesNullWithoutConstraints(): void
    {
        $property = new ObjectProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, null)->isValid()
        );
    }

    #[DataProvider('nonEmptyValidValueProvider')]
    public function testNonNullablePropertyEnforcesInvalidatingValueConstraints(array|object $value): void
    {
        $property = new ObjectProperty('foo', false);

        $path = $this->createPathMock();
        $constraint = $this->createMock(ObjectValueConstraintInterface::class);

        $path
            ->method('__toString')
            ->willReturn('$.test');

        $constraint
            ->method('mapValueMatchesConstraint')
            ->willReturn(false);

        $constraint
            ->method('getMismatchExplanation')
            ->willReturnCallback(function (PathInterface $path, string|int $value) {
                return \sprintf('%s was tested with value %s', $path->__toString(), $value);
            });

        $property = $property->withValueConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        $this->assertFalse($result->isValid());

        $this->assertSame(
            \sprintf('$.test was tested with value %s', \current((array)$value)),
            $result->getValidationErrors()[0]
        );
    }

    #[DataProvider('nonEmptyValidValueProvider')]
    public function testNullablePropertyEnforcesInvalidatingValueConstraints(array|object|null $value): void
    {
        $property = new ObjectProperty('foo', true);

        $path = $this->createPathMock();
        $constraint = $this->createMock(ObjectValueConstraintInterface::class);

        $path
            ->method('__toString')
            ->willReturn('$.test');

        $constraint
            ->method('mapValueMatchesConstraint')
            ->willReturn(false);

        $constraint
            ->method('getMismatchExplanation')
            ->willReturnCallback(function (PathInterface $path, string|int $value) {
                return \sprintf('%s was tested with value %s', $path->__toString(), $value);
            });

        $property = $property->withValueConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        $this->assertFalse($result->isValid());

        $this->assertSame(
            \sprintf('$.test was tested with value %s', \current((array)$value)),
            $result->getValidationErrors()[0]
        );
    }

    #[DataProvider('validValueProvider')]
    public function testNonNullablePropertyEnforcesValidatingValueConstraints(array|object|null $value): void
    {
        $property = new ObjectProperty('foo', false);

        $path = $this->createPathMock();
        $constraint = $this->createMock(ObjectValueConstraintInterface::class);

        $path
            ->expects($this->never())
            ->method('__toString');

        $constraint
            ->method('mapValueMatchesConstraint')
            ->willReturn(true);

        $constraint
            ->expects($this->never())
            ->method('getMismatchExplanation');

        $property = $property->withValueConstraint($constraint)->withValueConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        $this->assertTrue($result->isValid());
        $this->assertCount(0, $result->getValidationErrors());
    }

    #[DataProvider('validValueProvider')]
    public function testNullablePropertyEnforcesValidatingValueConstraints(array|object|null $value): void
    {
        $property = new ObjectProperty('foo', true);

        $path = $this->createPathMock();
        $constraint = $this->createMock(ObjectValueConstraintInterface::class);

        $path
            ->expects($this->never())
            ->method('__toString');

        $constraint
            ->method('mapValueMatchesConstraint')
            ->willReturn(true);

        $constraint
            ->expects($this->never())
            ->method('getMismatchExplanation');

        $property = $property->withValueConstraint($constraint)->withValueConstraint($constraint);

        $result = $property->checkValueAgainstConstraints($path, $value);

        $this->assertTrue($result->isValid());
        $this->assertCount(0, $result->getValidationErrors());
    }

    public function testConstraintErrorsCompoundForAllValues(): void
    {
        $property = new ObjectProperty('foo', false);

        $constraint = $this->createMock(ObjectValueConstraintInterface::class);

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

        $property = $property->withValueConstraint($constraint);

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

        $this->assertSame('$.foo["0"] was tested with value "first"', $result->getValidationErrors()[0]);
        $this->assertSame('$.foo["1"] was tested with value 3', $result->getValidationErrors()[1]);
        $this->assertSame('$.foo["2"] was tested with value []', $result->getValidationErrors()[2]);
    }

    public function testConstraintCombinations(): void
    {
        $property = new ObjectProperty('foo', false);

        $keyConstraint = $this->createMock(ObjectKeyConstraintInterface::class);

        $keyConstraint
            ->expects($this->exactly(3))
            ->method('mapKeyMatchesConstraint')
            ->willReturnCallback(fn ($key) => $key === 'foo');

        $keyConstraint
            ->expects($this->exactly(2))
            ->method('getMismatchExplanation')
            ->willReturn('keyError');

        $valueConstraint = $this->createMock(ObjectValueConstraintInterface::class);

        $valueConstraint
            ->expects($this->exactly(3))
            ->method('mapValueMatchesConstraint')
            ->willReturnCallback(fn ($value) => $value === 'foo');

        $valueConstraint
            ->expects($this->exactly(2))
            ->method('getMismatchExplanation')
            ->willReturn('valueError');

        $property = $property
            ->withValueConstraint($valueConstraint)
            ->withKeyConstraint($keyConstraint);

        $path = new Root();

        $result = $property->checkValueAgainstConstraints(
            $path->withObjectKey('foo'),
            (object) [
                'foo' => 'bar',
                'bar' => 'baz',
                'key' => 'foo',
            ],
        );

        $this->assertFalse($result->isValid());
        $this->assertCount(4, $result->getValidationErrors());

        $this->assertCount(
            2,
            \array_filter(
                $result->getValidationErrors(),
                fn ($message) => $message === 'keyError',
            )
        );

        $this->assertCount(
            2,
            \array_filter(
                $result->getValidationErrors(),
                fn ($message) => $message === 'valueError',
            )
        );
    }

    #[DataProvider('normalisationCasesWithoutDefault')]
    public function testNormalisingToPhpValueWithoutDefault(mixed $value, mixed $expected): void
    {
        $property = new ObjectProperty('foo', true);

        $this->assertEquals($expected, $property->normalisePhpValue($value));
    }

    #[DataProvider('jsonValueProvider')]
    public function testNormalisingToJsonReturnsSameValue(mixed $value): void
    {
        $property = new ObjectProperty('foo', true);

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
        $object = new \stdClass();

        return [
            'int' => [123, $object],
            'float' => [123.00012, $object],
            'int string' => ['123', $object],
            'float string' => ['12.34', $object],
            'list[]' => [['foo', 'bar'], (object)['0' => 'foo', '1' => 'bar']],
            'hash[]' => [['foo' => 'bar'], (object)['foo' =>'bar']],
            'object' => [(object) ['prop' => 'value'], (object)['prop' => 'value']],
            'true' => [true, $object],
            'false' => [false, $object],
            'null' => [null, null],
            'class' => [
                new class {
                    public string $thing = 'value';
                    protected int $extensible = 8;
                    private array $secret = [];
                },
                (object)['thing' => 'value'],
            ]
        ];
    }

    public static function validValueProvider(): \Generator
    {
        foreach ([[], new \stdClass()] as $value) {
            yield \str_replace("\n", ' ', \var_export($value, true)) => [$value];
        }

        return self::nonEmptyValidValueProvider();
    }

    public static function nonEmptyValidValueProvider(): \Generator
    {
        $cases = [
            (object)['foo', 'bar'],
            ['foo', 'bar'],
            (object)['default' => 'value'],
            ['default' => 'value'],
            new class {
                public string $thing = 'value';
                protected int $extensible = 8;
                private array $secret = [];
            }
        ];

        foreach ($cases as $value) {
            yield \str_replace("\n", ' ', \var_export($value, true)) => [$value];
        }
    }

    public static function invalidValueProvider(): \Generator
    {
        foreach (['0', '1', '-1', '2.3', '-85.25', ' ', '', 1, -1, 1.1, -1.1, false, true] as $value) {
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
