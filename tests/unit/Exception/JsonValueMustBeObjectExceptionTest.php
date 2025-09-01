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
        $exception = JsonValueMustBeObjectException::new($value);

        $this->assertInstanceOf(JsonValueMustBeObjectException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $expectedMessage = \sprintf(
            'JSON string must decode to an object, but resulting type was %s.',
            $expectedType
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionHierarchy(): void
    {
        $exception = JsonValueMustBeObjectException::new('string');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testMessageFormat(): void
    {
        $exception = JsonValueMustBeObjectException::new(123);

        $message = $exception->getMessage();

        $this->assertStringStartsWith('JSON string must decode to an object', $message);
        $this->assertStringContainsString('but resulting type was', $message);
        $this->assertStringEndsWith('.', $message);
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
}

