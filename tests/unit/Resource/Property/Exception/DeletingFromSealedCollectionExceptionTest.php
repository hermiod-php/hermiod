<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property\Exception;

use Hermiod\Resource\Property\CollectionInterface;
use Hermiod\Resource\Property\Exception\DeletingFromSealedCollectionException;
use Hermiod\Resource\Property\Exception\Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeletingFromSealedCollectionException::class)]
final class DeletingFromSealedCollectionExceptionTest extends TestCase
{
    #[DataProvider('offsetProvider')]
    public function testNewWithVariousOffsets(mixed $offset, string $expectedOffsetInMessage): void
    {
        $collection = $this->createMock(CollectionInterface::class);

        $exception = DeletingFromSealedCollectionException::new($collection, $offset);

        $this->assertInstanceOf(DeletingFromSealedCollectionException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $message = $exception->getMessage();

        $this->assertStringContainsString('Failed to remove', $message);
        $this->assertStringContainsString($expectedOffsetInMessage, $message);
        $this->assertStringContainsString('The collection is sealed', $message);
        $this->assertStringContainsString('new properties cannot be added', $message);
    }

    public function testExceptionHierarchy(): void
    {
        $collection = $this->createMock(CollectionInterface::class);
        $exception = DeletingFromSealedCollectionException::new($collection, 'key');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(\LogicException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testMessageFormat(): void
    {
        $collection = $this->createMock(CollectionInterface::class);
        $exception = DeletingFromSealedCollectionException::new($collection, 'test_key');

        $message = $exception->getMessage();

        $this->assertStringStartsWith('Failed to remove', $message);
        $this->assertStringContainsString('[', $message);
        $this->assertStringContainsString(']', $message);
        $this->assertStringContainsString('The collection is sealed', $message);
    }

    public function testMessageContainsCollectionClass(): void
    {
        $collection = $this->createMock(CollectionInterface::class);
        $exception = DeletingFromSealedCollectionException::new($collection, 'key');

        $message = $exception->getMessage();

        // Mock class names will contain the mock class pattern
        $this->assertStringContainsString('MockObject_CollectionInterface_', $message);
    }

    public static function offsetProvider(): array
    {
        $resource = \fopen('php://memory', 'r');
        \fclose($resource);

        return [
            // String and numeric offsets (converted to string)
            'string offset' => ['property_name', 'property_name'],
            'integer offset' => [42, '42'],
            'zero offset' => [0, '0'],
            'negative integer' => [-1, '-1'],
            'float offset' => [3.14, '3.14'],
            'numeric string' => ['123', '123'],
            'empty string' => ['', ''],
            'large integer' => [\PHP_INT_MAX, (string)\PHP_INT_MAX],

            // Non-string/non-numeric offsets (converted to type)
            'null offset' => [null, 'NULL'],
            'boolean true' => [true, 'boolean'],
            'boolean false' => [false, 'boolean'],
            'array offset' => [['key' => 'value'], 'array'],
            'object offset' => [new \stdClass(), 'object'],
            'resource offset' => [$resource, 'resource (closed)'],

            // Complex arrays and objects
            'nested array' => [['a' => ['b' => 'c']], 'array'],
            'indexed array' => [['one', 'two'], 'array'],
            'empty array' => [[], 'array'],
            'DateTime object' => [new \DateTime(), 'object'],

            // Special numeric values
            'infinity' => [\INF, 'INF'],
            'negative infinity' => [-\INF, '-INF'],
            'not a number' => [\NAN, 'NAN'],

            // Edge cases
            'closure' => [fn() => null, 'object'],
        ];
    }
}
