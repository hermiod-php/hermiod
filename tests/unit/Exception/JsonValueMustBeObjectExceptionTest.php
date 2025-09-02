<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Exception;

use Hermiod\Exception\Exception;
use Hermiod\Exception\JsonValueMustBeObjectException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonValueMustBeObjectException::class)]
final class JsonValueMustBeObjectExceptionTest extends TestCase
{
    #[DataProvider('valueTypeProvider')]
    public function testNewWithVariousTypes(mixed $value, string $expectedType): void
    {
        $exception = JsonValueMustBeObjectException::invalidType($value);

        $this->assertInstanceOf(JsonValueMustBeObjectException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $expectedMessage = \sprintf(
            'JSON string must decode to an object, but resulting type was %s.',
            $expectedType
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    #[DataProvider('jsonErrorProvider')]
    public function testInvalidJsonWithVariousErrors(string $error): void
    {
        $exception = JsonValueMustBeObjectException::invalidJson($error);

        $this->assertInstanceOf(JsonValueMustBeObjectException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $expectedMessage = \sprintf(
            "JSON string must decode to an object, but decoding failed with '%s'",
            $error
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionHierarchy(): void
    {
        $exception = JsonValueMustBeObjectException::invalidType('string');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testInvalidTypeMessageFormat(): void
    {
        $exception = JsonValueMustBeObjectException::invalidType(123);

        $message = $exception->getMessage();

        $this->assertStringStartsWith('JSON string must decode to an object', $message);
        $this->assertStringContainsString('but resulting type was', $message);
        $this->assertStringEndsWith('.', $message);
    }

    public function testInvalidJsonMessageFormat(): void
    {
        $exception = JsonValueMustBeObjectException::invalidJson('Syntax error');

        $message = $exception->getMessage();

        $this->assertStringStartsWith('JSON string must decode to an object', $message);
        $this->assertStringContainsString('but decoding failed with', $message);
        $this->assertStringContainsString("'Syntax error'", $message);
    }

    public function testInvalidJsonWithEmptyError(): void
    {
        $exception = JsonValueMustBeObjectException::invalidJson('');

        $expectedMessage = "JSON string must decode to an object, but decoding failed with ''";
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testInvalidJsonWithSpecialCharacters(): void
    {
        $error = "Error with \"quotes\" and 'apostrophes' and\nnewlines";
        $exception = JsonValueMustBeObjectException::invalidJson($error);

        $expectedMessage = \sprintf(
            "JSON string must decode to an object, but decoding failed with '%s'",
            $error
        );
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public static function valueTypeProvider(): array
    {
        $resource = \fopen('php://memory', 'r');

        \fclose($resource);

        return [
            // Basic types
            'string' => ['hello', 'string'],
            'integer' => [42, 'integer'],
            'float' => [3.14, 'double'],
            'boolean true' => [true, 'boolean'],
            'boolean false' => [false, 'boolean'],
            'null' => [null, 'NULL'],
            'array' => [['key' => 'value'], 'array'],

            // Edge cases
            'empty string' => ['', 'string'],
            'zero integer' => [0, 'integer'],
            'zero float' => [0.0, 'double'],
            'empty array' => [[], 'array'],
            'numeric string' => ['123', 'string'],
            'float as string' => ['3.14', 'string'],

            // Special values
            'negative integer' => [-1, 'integer'],
            'negative float' => [-2.5, 'double'],
            'large integer' => [\PHP_INT_MAX, 'integer'],
            'infinity' => [\INF, 'double'],
            'negative infinity' => [-\INF, 'double'],
            'not a number' => [\NAN, 'double'],

            // Objects
            'stdClass object' => [new \stdClass(), 'object'],
            'DateTime object' => [new \DateTime(), 'object'],
            'closure' => [fn() => null, 'object'],

            // Complex arrays
            'nested array' => [['a' => ['b' => 'c']], 'array'],
            'indexed array' => [['one', 'two', 'three'], 'array'],
            'mixed array' => [['string', 123, true, null], 'array'],

            // Resource (closed)
            'closed resource' => [$resource, 'resource (closed)'],
        ];
    }

    public static function jsonErrorProvider(): array
    {
        return [
            'syntax error' => ['Syntax error'],
            'unexpected token' => ['Unexpected token'],
            'malformed json' => ['Malformed JSON'],
            'invalid escape sequence' => ['Invalid escape sequence'],
            'maximum depth exceeded' => ['Maximum stack depth exceeded'],
            'control character error' => ['Control character error'],
            'state mismatch' => ['State mismatch'],
            'utf8 error' => ['Malformed UTF-8 characters'],
            'empty error' => [''],
            'error with quotes' => ['Error with "quotes" in message'],
            'error with apostrophes' => ["Error with 'apostrophes' in message"],
            'error with newlines' => ["Error with\nnewlines"],
            'error with tabs' => ["Error with\ttabs"],
            'long error message' => ['This is a very long error message that describes in detail what went wrong during JSON parsing and why the operation failed'],
            'unicode error' => ['Error with unicode: ñáéíóú'],
            'special characters' => ['Error with special chars: !@#$%^&*()'],
        ];
    }
}

