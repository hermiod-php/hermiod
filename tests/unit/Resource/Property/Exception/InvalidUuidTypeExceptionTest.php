<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property\Exception;

use Hermiod\Resource\Property\Exception\Exception;
use Hermiod\Resource\Property\Exception\InvalidUuidTypeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

#[CoversClass(InvalidUuidTypeException::class)]
final class InvalidUuidTypeExceptionTest extends TestCase
{
    #[DataProvider('invalidTypeProvider')]
    public function testNewWithVariousTypes(mixed $supplied, string $expectedTypeInMessage): void
    {
        $exception = InvalidUuidTypeException::new($supplied);

        $this->assertInstanceOf(InvalidUuidTypeException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $message = $exception->getMessage();

        $this->assertStringContainsString($expectedTypeInMessage, $message);
        $this->assertStringContainsString('is not a valid type for use with ramsey/uuid', $message);
        $this->assertStringContainsString('Expected instance of', $message);
        $this->assertStringContainsString(UuidInterface::class, $message);
        $this->assertStringContainsString('or string', $message);
    }

    public function testExceptionHierarchy(): void
    {
        $exception = InvalidUuidTypeException::new(123);

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(\LogicException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testMessageFormat(): void
    {
        $exception = InvalidUuidTypeException::new(123);

        $message = $exception->getMessage();

        $this->assertStringEndsWith('or string.', $message);
        $this->assertStringContainsString('integer is not a valid type', $message);
    }

    public function testWithCustomObject(): void
    {
        $object = new \stdClass();
        $exception = InvalidUuidTypeException::new($object);

        $message = $exception->getMessage();

        $this->assertStringContainsString('stdClass is not a valid type', $message);
        $this->assertStringContainsString(UuidInterface::class, $message);
    }

    public function testWithComplexObject(): void
    {
        $object = new \DateTime();
        $exception = InvalidUuidTypeException::new($object);

        $message = $exception->getMessage();

        $this->assertStringContainsString('DateTime is not a valid type', $message);
    }

    public static function invalidTypeProvider(): array
    {
        $resource = \fopen('php://memory', 'r');
        \fclose($resource);

        $anonymousClass = new class {};
        $anonymousClassName = \get_class($anonymousClass);

        return [
            // Primitive types
            'integer' => [42, 'integer'],
            'float' => [3.14, 'double'],
            'boolean true' => [true, 'boolean'],
            'boolean false' => [false, 'boolean'],
            'null' => [NULL, 'NULL'],
            'array' => [['uuid' => 'value'], 'array'],

            // Edge case primitives
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
            'DateTime' => [new \DateTime(), 'DateTime'],
            'ArrayObject' => [new \ArrayObject(), 'ArrayObject'],
            'SplFixedArray' => [new \SplFixedArray(0), 'SplFixedArray'],
            'Exception' => [new \Exception(), 'Exception'],
            'closure' => [fn() => null, 'Closure'],

            // Complex arrays
            'nested array' => [['nested' => ['uuid' => 'value']], 'array'],
            'indexed array' => [['uuid1', 'uuid2', 'uuid3'], 'array'],
            'mixed array' => [['string', 123, true, null], 'array'],

            // Resource
            'closed resource' => [$resource, 'resource (closed)'],

            // Invalid UUID-like strings (still strings, but would be invalid UUIDs)
            'empty string' => ['', 'string'],
            'invalid uuid string' => ['not-a-valid-uuid', 'string'],
            'partial uuid' => ['123e4567-e89b-12d3', 'string'],
            'malformed uuid' => ['123e4567-e89b-12d3-a456-426614174000-extra', 'string'],

            // Strings with special characters that might be confused with UUIDs
            'uuid-like with invalid chars' => ['123e4567-e89b-12d3-a456-42661417400g', 'string'],
            'hex string' => ['deadbeef', 'string'],
            'base64 string' => ['SGVsbG8gV29ybGQ=', 'string'],

            // Anonymous class
            'anonymous class' => [$anonymousClass, $anonymousClassName],
        ];
    }
}
