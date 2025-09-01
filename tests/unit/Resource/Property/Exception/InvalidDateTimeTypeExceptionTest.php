<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property\Exception;

use Hermiod\Resource\Property\Exception\Exception;
use Hermiod\Resource\Property\Exception\InvalidDateTimeTypeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidDateTimeTypeException::class)]
final class InvalidDateTimeTypeExceptionTest extends TestCase
{
    #[DataProvider('invalidTypeProvider')]
    public function testNewWithVariousTypes(mixed $supplied, string $expectedTypeInMessage): void
    {
        $exception = InvalidDateTimeTypeException::new($supplied);

        $this->assertInstanceOf(InvalidDateTimeTypeException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $message = $exception->getMessage();

        $this->assertStringContainsString($expectedTypeInMessage, $message);
        $this->assertStringContainsString('is not a valid datetime type', $message);
        $this->assertStringContainsString('Only ISO 8601 date strings and instances of', $message);
        $this->assertStringContainsString(\DateTimeInterface::class, $message);
        $this->assertStringContainsString('are acceptable', $message);
    }

    public function testExceptionHierarchy(): void
    {
        $exception = InvalidDateTimeTypeException::new('invalid');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(\LogicException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testMessageFormat(): void
    {
        $exception = InvalidDateTimeTypeException::new(123);

        $message = $exception->getMessage();

        $this->assertStringEndsWith('are acceptable.', $message);
        $this->assertStringContainsString('integer is not a valid datetime type', $message);
    }

    public function testWithCustomObject(): void
    {
        $object = new \stdClass();
        $exception = InvalidDateTimeTypeException::new($object);

        $message = $exception->getMessage();

        $this->assertStringContainsString('stdClass is not a valid datetime type', $message);
        $this->assertStringContainsString(\DateTimeInterface::class, $message);
    }

    public function testWithComplexObject(): void
    {
        $object = new \ArrayObject();
        $exception = InvalidDateTimeTypeException::new($object);

        $message = $exception->getMessage();

        $this->assertStringContainsString('ArrayObject is not a valid datetime type', $message);
    }

    public static function invalidTypeProvider(): array
    {
        $resource = \fopen('php://memory', 'r');
        \fclose($resource);

        $anonymousClass = new class {};
        $anonymousClassName = \get_class($anonymousClass);

        return [
            // Primitive types (lowercase)
            'string' => ['not-a-date', 'string'],
            'integer' => [42, 'integer'],
            'float' => [3.14, 'double'],
            'boolean true' => [true, 'boolean'],
            'boolean false' => [false, 'boolean'],
            'null' => [null, 'null'],
            'array' => [['date' => '2023-01-01'], 'array'],

            // Edge case primitives
            'empty string' => ['', 'string'],
            'zero integer' => [0, 'integer'],
            'zero float' => [0.0, 'double'],
            'empty array' => [[], 'array'],
            'numeric string' => ['123', 'string'],
            'float string' => ['3.14', 'string'],

            // Special numeric values
            'negative integer' => [-1, 'integer'],
            'negative float' => [-2.5, 'double'],
            'large integer' => [\PHP_INT_MAX, 'integer'],
            'infinity' => [\INF, 'double'],
            'negative infinity' => [-\INF, 'double'],
            'not a number' => [\NAN, 'double'],

            // Objects (class names)
            'stdClass' => [new \stdClass(), 'stdClass'],
            'ArrayObject' => [new \ArrayObject(), 'ArrayObject'],
            'SplFixedArray' => [new \SplFixedArray(0), 'SplFixedArray'],
            'Exception' => [new \Exception(), 'Exception'],
            'closure' => [fn() => null, 'Closure'],

            // Complex arrays
            'nested array' => [['nested' => ['date' => '2023-01-01']], 'array'],
            'indexed array' => [['2023', '01', '01'], 'array'],
            'mixed array' => [['string', 123, true, null], 'array'],

            // Resource
            'closed resource' => [$resource, 'resource (closed)'],

            // Date-like strings that are invalid
            'invalid date string' => ['not-a-valid-date', 'string'],
            'partial date' => ['2023-01', 'string'],
            'wrong format' => ['01/01/2023', 'string'],
            'time only' => ['12:30:45', 'string'],

            // Objects that might be confused with DateTime
            'DateTime mock' => [$anonymousClass, $anonymousClassName],
        ];
    }
}
