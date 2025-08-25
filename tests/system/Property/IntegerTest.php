<?php

declare(strict_types=1);

namespace Hermiod\Tests\System\Property;

use Hermiod\Converter;
use Hermiod\ConverterInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Hermiod\Tests\System\Fakes\IntegerPropertiesFake;

final class IntegerTest extends TestCase
{
    private const VALID_JSON = [
        'nullableInt' => null,
        'nullableIntDefaultNull' => null,
        'nullableIntDefaultInt' => 99,
        'nonNullableInt' => 0,
        'nonNullableIntDefaultInt' => 99,
        'intGreaterThanTwo' => 3,
        'intLessThanTwo' => 1,
        'intGreaterThanOneLessThanThree' => 2,
        'intInList' => 5,
        'intGreaterThanOrEqualFive' => 5,
        'intLessThanOrEqualFive' => 5,
    ];

    public static ConverterInterface $converter;

    public static function setUpBeforeClass(): void
    {
        self::$converter = Converter::create();
    }

    #[TestWith([null, null], 'null')]
    #[TestWith([0, 0], '0')]
    #[TestWith([-2, -2], '-2')]
    #[TestWith([\PHP_INT_MIN, \PHP_INT_MIN], "\PHP_INT_MIN")]
    #[TestWith([\PHP_INT_MAX, \PHP_INT_MAX], "\PHP_INT_MAX")]
    public function testNullableIntValidValues(int|null $value, int|null $expected): void
    {
        $property = 'nullableInt';

        $result = self::$converter->toClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, $value));

        $this->assertInstanceOf(IntegerPropertiesFake::class, $result);
        $this->assertSame($expected, $result->get($property));
    }

    #[DataProvider('nonIntegerProvider')]
    public function testNullableIntInvalidValues(string $property, mixed $value): void
    {
        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, $value));

        $this->assertFalse($result->isValid());

        $this->assertSame(
            \sprintf('$.%s must be an integer but %s given', $property, \get_debug_type($value)),
            \iterator_to_array($result->getErrors())[0]->getMessage(),
        );
    }

    #[TestWith(['nullableInt', null])]
    #[TestWith(['nullableIntDefaultNull', null])]
    #[TestWith(['nullableIntDefaultInt', 99])]
    #[TestWith(['nonNullableIntDefaultInt', 97])]
    public function testNullableOrDefaultIntIsNotRequired(string $property, mixed $expected): void
    {
        $result = self::$converter->toClass(IntegerPropertiesFake::class, $this->generateJsonWithout($property));

        $this->assertInstanceOf(IntegerPropertiesFake::class, $result);
        $this->assertSame($expected, $result->get($property));
    }

    public function testNonNullableWithoutDefaultIsRequired(): void
    {
        $property = 'nonNullableInt';

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWithout($property));

        $this->assertFalse($result->isValid());

        $this->assertSame(
            \sprintf('$.%s is required', $property),
            \iterator_to_array($result->getErrors())[0]->getMessage(),
        );
    }

    public function testBoundsOfIntGreaterThanTwo(): void
    {
        $property = 'intGreaterThanTwo';

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 2));

        $this->assertFalse($result->isValid());

        $this->assertSame(
            \sprintf('$.%s must be a number greater than 2 but 2 given', $property),
            \iterator_to_array($result->getErrors())[0]->getMessage(),
        );

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 3));

        $this->assertTrue($result->isValid());
    }

    public function testBoundsOfIntLessThanTwo(): void
    {
        $property = 'intLessThanTwo';

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 2));

        $this->assertFalse($result->isValid());

        $this->assertSame(
            \sprintf('$.%s must be a number less than 2 but 2 given', $property),
            \iterator_to_array($result->getErrors())[0]->getMessage(),
        );

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 1));

        $this->assertTrue($result->isValid());
    }

    public function testBoundsOfIntGreaterThanOrEqualFive(): void
    {
        $property = 'intGreaterThanOrEqualFive';

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 2));

        $this->assertFalse($result->isValid());

        $this->assertSame(
            \sprintf('$.%s must be a number greater than or equal to 5 but 2 given', $property),
            \iterator_to_array($result->getErrors())[0]->getMessage(),
        );

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 5));

        $this->assertTrue($result->isValid());

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 6));

        $this->assertTrue($result->isValid());
    }

    public function testBoundsOfIntLessThanOrEqualFive(): void
    {
        $property = 'intLessThanOrEqualFive';

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 6));

        $this->assertFalse($result->isValid());

        $this->assertSame(
            \sprintf('$.%s must be a number less than or equal to 5 but 6 given', $property),
            \iterator_to_array($result->getErrors())[0]->getMessage(),
        );

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 5));

        $this->assertTrue($result->isValid());

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 4));

        $this->assertTrue($result->isValid());
    }

    public function testBoundsOfIntGreaterThanOneLessThanThree(): void
    {
        $property = 'intGreaterThanOneLessThanThree';

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 1));

        $this->assertFalse($result->isValid());

        $this->assertSame(
            \sprintf('$.%s must be a number greater than 1 but 1 given', $property),
            \iterator_to_array($result->getErrors())[0]->getMessage(),
        );

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 3));

        $this->assertFalse($result->isValid());

        $this->assertSame(
            \sprintf('$.%s must be a number less than 3 but 3 given', $property),
            \iterator_to_array($result->getErrors())[0]->getMessage(),
        );

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 2));

        $this->assertTrue($result->isValid());
    }

    public function testBoundsOfIntInList(): void
    {
        $property = 'intInList';

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 4));

        $this->assertFalse($result->isValid());

        $this->assertSame(
            \sprintf('$.%s must be one of [5, 6, 7] but 4 given', $property),
            \iterator_to_array($result->getErrors())[0]->getMessage(),
        );

        $result = self::$converter->tryToClass(IntegerPropertiesFake::class, $this->generateJsonWith($property, 8));

        $this->assertFalse($result->isValid());

        $this->assertSame(
            \sprintf('$.%s must be one of [5, 6, 7] but 8 given', $property),
            \iterator_to_array($result->getErrors())[0]->getMessage(),
        );

        foreach ([5, 6, 7] as $value) {
            $result = self::$converter->tryToClass(
                IntegerPropertiesFake::class,
                $this->generateJsonWith($property, $value),
            );

            $this->assertTrue($result->isValid());
        }
    }

    public static function nonIntegerProvider(): \Generator
    {
        $properties = [
            'nullableInt',
            'nullableIntDefaultNull',
            'nullableIntDefaultInt',
            'nonNullableIntDefaultInt',
        ];

        $types = [
            'string',
            0.0,
            1.1,
            -1.1,
            false,
            true,
            [],
            (object)[],
        ];

        foreach ($properties as $property) {
            foreach ($types as $type) {
                yield \sprintf('setting %s to %s', $property, \json_encode($type)) => [$property, $type];
            }
        }
    }

    private function generateJsonWith(string $key, mixed $value): array
    {
        $json = self::VALID_JSON;

        $json[$key] = $value;

        return $json;
    }

    private function generateJsonWithout(string $key): array
    {
        $json = self::VALID_JSON;

        unset($json[$key]);

        return $json;
    }
}
