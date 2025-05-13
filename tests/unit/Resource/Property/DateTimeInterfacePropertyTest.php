<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\DateTimeInterfaceProperty;
use Hermiod\Resource\Property\Traits\ConstructWithNameAndNullableTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateTimeInterfaceProperty::class)]
#[CoversClass(ConstructWithNameAndNullableTrait::class)]
class DateTimeInterfacePropertyTest extends TestCase
{
    private const ISO_8601_FORMAT_WITH_MILLISECONDS = 'Y-m-d\TH:i:s.vP';

    public function testImplementsPropertyInterface(): void
    {
        $this->assertInstanceOf(
            PropertyInterface::class,
            new DateTimeInterfaceProperty('foo', false),
        );
    }

    public function testGetPropertyName(): void
    {
        $property = new DateTimeInterfaceProperty('foo', false);

        $this->assertSame('foo', $property->getPropertyName());
    }

    public function testGetDefaultValueWhenNoneSet(): void
    {
        $property = new DateTimeInterfaceProperty('foo', false);

        $this->assertFalse($property->hasDefaultValue());;
        $this->assertNull($property->getDefaultValue());
    }

    public function testGetDefaultValueWhenSet(): void
    {
        $property = DateTimeInterfaceProperty::withDefaultNullValue('foo');

        $this->assertTrue($property->hasDefaultValue());
        $this->assertNull($property->getDefaultValue());
    }

    #[DataProvider('validValueProvider')]
    public function testNonNullablePropertyValidatesDatesWithoutConstraints(\DateTimeInterface|string $value): void
    {
        $property = new DateTimeInterfaceProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('invalidValueProvider')]
    public function testNonNullablePropertyDoesNotValidateDatesWithoutConstraints(mixed $value): void
    {
        $property = new DateTimeInterfaceProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    public function testNonNullablePropertyDoesNotValidateNullWithoutConstraints(): void
    {
        $property = new DateTimeInterfaceProperty('foo', false);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, null)->isValid()
        );
    }

    #[DataProvider('validValueProvider')]
    public function testNullablePropertyValidatesDatesWithoutConstraints(\DateTimeInterface|string $value): void
    {
        $property = new DateTimeInterfaceProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    #[DataProvider('invalidValueProvider')]
    public function testNullablePropertyDoesNotValidateNonDatesWithoutConstraints(mixed $value): void
    {
        $property = new DateTimeInterfaceProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertFalse(
            $property->checkValueAgainstConstraints($path, $value)->isValid()
        );
    }

    public function testNullablePropertyValidatesNullWithoutConstraints(): void
    {
        $property = new DateTimeInterfaceProperty('foo', true);
        $path = $this->createMock(PathInterface::class);

        $this->assertTrue(
            $property->checkValueAgainstConstraints($path, null)->isValid()
        );
    }

    #[DataProvider('normalisationCasesWithoutDefault')]
    public function testNormalisingToPhpValueWithoutDefault(string $value, \DateTimeInterface $expected): void
    {
        $property = new DateTimeInterfaceProperty('foo', false);

        $normalised = $property->normalisePhpValue($value);

        $this->assertInstanceOf(\DateTimeInterface::class, $normalised);
        $this->assertEquals($expected, $normalised);
    }

    #[DataProvider('normalisationCasesWithDefault')]
    public function testNormalisingToPhpValueWithDefaultNull(string|null $value, \DateTimeInterface|null $expected): void
    {
        $property = DateTimeInterfaceProperty::withDefaultNullValue('foo');

        $normalised = $property->normalisePhpValue($value);

        $this->assertSame(
            $expected->format(self::ISO_8601_FORMAT_WITH_MILLISECONDS),
            $normalised->format(self::ISO_8601_FORMAT_WITH_MILLISECONDS),
        );
    }

    public static function normalisationCasesWithoutDefault(): \Generator
    {
        $cases = [
            '2025' => '2025-01-01T00:00:00+00:00',
            '2025-01' => '2025-01-01T00:00:00+00:00',
            '2025-06-30' => '2025-06-30T00:00:00+00:00',
            '2025-06-30 00:10:10' => '2025-06-30T00:10:10+00:00',
            '2025-06-30T00:10:10' => '2025-06-30T00:10:10+00:00',
            '2025-06-30 00:10:10.001' => '2025-06-30T00:10:10.001+00:00',
            '2025-06-30T00:10:10+01:30' => '2025-06-30T00:10:10+01:30',
            '2025-06-30 00:10:10+01:30' => '2025-06-30T00:10:10+01:30',
            '2025-06-30T00:10:10.001-02:45' => '2025-06-30T00:10:10.001-02:45',
            '2025-06-30 00:10:10.001-02:45' => '2025-06-30T00:10:10.001-02:45',
        ];

        foreach ($cases as $from => $to) {
            yield \sprintf("'%s', expected '%s'", $from, $to) => [
                \strval($from),
                new \DateTimeImmutable($to),
            ];
        }
    }

    public static function normalisationCasesWithDefault(): \Generator
    {
        $cases = [
            '2025-06-30' => '2025-06-30T00:00:00+00:00',
            '2025-06-30 00:10:10' => '2025-06-30T00:10:10+00:00',
            '2025-06-30T00:10:10' => '2025-06-30T00:10:10+00:00',
            '2025-06-30 00:10:10.001' => '2025-06-30T00:10:10.001+00:00',
            '2025-06-30T00:10:10+01:30' => '2025-06-30T00:10:10+01:30',
            '2025-06-30 00:10:10+01:30' => '2025-06-30T00:10:10+01:30',
            '2025-06-30T00:10:10.001-02:45' => '2025-06-30T00:10:10.001-02:45',
            '2025-06-30 00:10:10.001-02:45' => '2025-06-30T00:10:10.001-02:45',
        ];

        foreach ($cases as $from => $to) {
            yield \sprintf("'%s', expected '%s'", $from, $to) => [
                \strval($from),
                new \DateTimeImmutable($to),
            ];
        }
    }

    public static function validValueProvider(): \Generator
    {
        $cases = [
            '0000',
            '2025',
            '2025-01',
            '2025-01-13',
            '2025-01-13T10:30:26',
            '2025-01-13 10:30:26.009',
            '2025-01-13 10:30:26.00009',
            '2025-01-13 10:30:26.001+01:00',
            '2025-01-13T10:30:26.001+01:00',
        ];

        foreach ($cases as $value) {
            yield \sprintf("string '%s'", $value) => [$value];
            yield \sprintf("%s('%s')", \DateTime::class, $value) => [new \DateTime($value)];
            yield \sprintf("%s('%s')", \DateTimeImmutable::class, $value) => [new \DateTimeImmutable($value)];
        }
    }

    public static function invalidValueProvider(): \Generator
    {
        $cases = [
            '10:20:35',
            '20255',
            '2d56',
            'xxxx',
            '2025-99-99',
            '86-12-09',
            '23rd January 2026',
            'Jan, 23 2027',
            '0',
            '1',
            '-1',
            '2.3',
            '-85.25',
            ' ',
            '',
        ];

        $types =  [
            [],
            (object)[],
            false,
            true,
            8,
            -8,
            20.32,
            -785.25,
        ];

        foreach (\array_merge($cases, $types) as $value) {
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
