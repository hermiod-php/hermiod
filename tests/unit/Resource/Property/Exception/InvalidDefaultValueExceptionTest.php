<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property\Exception;

use Hermiod\Resource\Property\Exception\Exception;
use Hermiod\Resource\Property\Exception\InvalidDefaultValueException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidDefaultValueException::class)]
final class InvalidDefaultValueExceptionTest extends TestCase
{
    #[DataProvider('invalidDefaultValueProvider')]
    public function testNewWithVariousParameters(
        string $type,
        mixed $given,
        bool $nullable,
        array $otherAcceptableTypes,
        string $expectedGivenType,
        string $expectedTypePhrase,
        string $expectedAcceptableTypes
    ): void {
        $exception = InvalidDefaultValueException::new($type, $given, $nullable, ...$otherAcceptableTypes);

        $this->assertInstanceOf(InvalidDefaultValueException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $message = $exception->getMessage();

        $this->assertStringContainsString("The value type ($expectedGivenType)", $message);
        $this->assertStringContainsString("property type ($type)", $message);
        $this->assertStringContainsString($expectedTypePhrase, $message);
        $this->assertStringContainsString($expectedAcceptableTypes, $message);
    }

    public function testExceptionHierarchy(): void
    {
        $exception = InvalidDefaultValueException::new('string', 123, false);

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(\LogicException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testMessageFormatWithSingleType(): void
    {
        $exception = InvalidDefaultValueException::new('string', 123, false);

        $message = $exception->getMessage();

        $this->assertStringContainsString('Acceptable type is (string)', $message);
        $this->assertStringNotContainsString('types are', $message);
    }

    public function testMessageFormatWithMultipleTypes(): void
    {
        $exception = InvalidDefaultValueException::new('string', 123, false, 'int', 'float');

        $message = $exception->getMessage();

        $this->assertStringContainsString('Acceptable types are', $message);
        $this->assertStringContainsString('(string), (int), (float)', $message);
        $this->assertStringNotContainsString('type is', $message);
    }

    public function testNullablePropertyWithNonNullDefault(): void
    {
        $exception = InvalidDefaultValueException::new('string', 123, true);

        $message = $exception->getMessage();

        $this->assertStringContainsString('(string), (null)', $message);
        $this->assertStringContainsString('types are', $message);
    }

    public function testNullablePropertyWithMultipleTypes(): void
    {
        $exception = InvalidDefaultValueException::new('string', 123, true, 'int', 'bool');

        $message = $exception->getMessage();

        $this->assertStringContainsString('(string), (int), (bool), (null)', $message);
    }

    public function testNonNullablePropertySingleType(): void
    {
        $exception = InvalidDefaultValueException::new('int', 'invalid', false);

        $message = $exception->getMessage();

        $this->assertStringContainsString('Acceptable type is (int)', $message);
        $this->assertStringNotContainsString('null', $message);
    }

    public static function invalidDefaultValueProvider(): array
    {
        $resource = \fopen('php://memory', 'r');
        \fclose($resource);

        return [
            // Basic type mismatches - non-nullable
            'string property with int value' => [
                'string',
                123,
                false,
                [],
                'int',
                'type is',
                '(string)'
            ],
            'int property with string value' => [
                'int',
                'invalid',
                false,
                [],
                'string',
                'type is',
                '(int)'
            ],
            'bool property with array value' => [
                'bool',
                ['invalid'],
                false,
                [],
                'array',
                'type is',
                '(bool)'
            ],

            // Basic type mismatches - nullable
            'nullable string with int' => [
                'string',
                123,
                true,
                [],
                'int',
                'types are',
                '(string), (null)'
            ],
            'nullable int with object' => [
                'int',
                new \stdClass(),
                true,
                [],
                'stdClass',
                'types are',
                '(int), (null)'
            ],

            // Multiple acceptable types - non-nullable
            'with additional types' => [
                'string',
                123.45,
                false,
                ['int', 'float'],
                'float',
                'types are',
                '(string), (int), (float)'
            ],
            'multiple types mismatch' => [
                'array',
                'invalid',
                false,
                ['object', 'null'],
                'string',
                'types are',
                '(array), (object), (null)'
            ],

            // Multiple acceptable types - nullable
            'nullable with multiple types' => [
                'string',
                [],
                true,
                ['int', 'bool'],
                'array',
                'types are',
                '(string), (int), (bool), (null)'
            ],

            // Complex types
            'object property with primitive' => [
                'DateTime',
                'not-object',
                false,
                ['DateTimeInterface'],
                'string',
                'types are',
                '(DateTime), (DateTimeInterface)'
            ],

            // Edge cases with special values
            'resource value' => [
                'string',
                $resource,
                false,
                [],
                'resource (closed)',
                'type is',
                '(string)'
            ],
            'closure value' => [
                'callable',
                fn() => null,
                false,
                ['string'],
                'Closure',
                'types are',
                '(callable), (string)'
            ],

            // Null handling
            'null value for non-nullable' => [
                'string',
                null,
                false,
                [],
                'null',
                'type is',
                '(string)'
            ],
            'null value with multiple types non-nullable' => [
                'string',
                null,
                false,
                ['int', 'bool'],
                'null',
                'types are',
                '(string), (int), (bool)'
            ],

            // Large number of acceptable types
            'many acceptable types' => [
                'string',
                123,
                true,
                ['int', 'float', 'bool', 'array', 'object'],
                'int',
                'types are',
                '(string), (int), (float), (bool), (array), (object), (null)'
            ],
        ];
    }
}

