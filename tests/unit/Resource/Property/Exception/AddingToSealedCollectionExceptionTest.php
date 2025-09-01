<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property\Exception;

use Hermiod\Resource\Property\CollectionInterface;
use Hermiod\Resource\Property\Exception\AddingToSealedCollectionException;
use Hermiod\Resource\Property\Exception\Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddingToSealedCollectionException::class)]
final class AddingToSealedCollectionExceptionTest extends TestCase
{
    #[DataProvider('addingParametersProvider')]
    public function testNewWithVariousParameters(
        string $collectionClass,
        mixed $offset,
        mixed $value,
        string $expectedOffsetInMessage,
        string $expectedValueInMessage
    ): void {
        $collection = $this->createMock(CollectionInterface::class);

        $exception = AddingToSealedCollectionException::new($collection, $offset, $value);

        $this->assertInstanceOf(AddingToSealedCollectionException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $message = $exception->getMessage();
        $this->assertStringContainsString('Failed to add', $message);
        $this->assertStringContainsString($expectedValueInMessage, $message);
        $this->assertStringContainsString($expectedOffsetInMessage, $message);
        $this->assertStringContainsString('The collection is sealed', $message);
        $this->assertStringContainsString('new properties cannot be added', $message);
    }

    public function testExceptionHierarchy(): void
    {
        $collection = $this->createMock(CollectionInterface::class);
        $exception = AddingToSealedCollectionException::new($collection, 'key', 'value');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(\LogicException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testMessageFormat(): void
    {
        $collection = $this->createMock(CollectionInterface::class);
        $exception = AddingToSealedCollectionException::new($collection, 'test_key', 'test_value');

        $message = $exception->getMessage();
        $this->assertStringStartsWith('Failed to add', $message);
        $this->assertStringContainsString('to', $message);
        $this->assertStringContainsString('[', $message);
        $this->assertStringContainsString(']', $message);
        $this->assertStringContainsString('The collection is sealed', $message);
    }

    public function testWithObjectValue(): void
    {
        $collection = $this->createMock(CollectionInterface::class);
        $value = new \stdClass();
        $exception = AddingToSealedCollectionException::new($collection, 'key', $value);

        $this->assertStringContainsString('stdClass', $exception->getMessage());
    }

    public function testWithCustomObject(): void
    {
        $collection = $this->createMock(CollectionInterface::class);
        $value = new \DateTime();
        $exception = AddingToSealedCollectionException::new($collection, 'key', $value);

        $this->assertStringContainsString('DateTime', $exception->getMessage());
    }

    public static function addingParametersProvider(): array
    {
        $resource = \fopen('php://memory', 'r');
        \fclose($resource);

        return [
            // String offsets
            'string offset with string value' => [
                'MockCollection',
                'property_name',
                'string_value',
                'property_name',
                'string'
            ],
            'string offset with integer value' => [
                'MockCollection',
                'index',
                42,
                'index',
                'integer'
            ],

            // Numeric offsets
            'integer offset with string value' => [
                'MockCollection',
                0,
                'value',
                '0',
                'string'
            ],
            'integer offset with array value' => [
                'MockCollection',
                5,
                ['key' => 'value'],
                '5',
                'array'
            ],

            // Various value types
            'null value' => [
                'MockCollection',
                'key',
                null,
                'key',
                'NULL'
            ],
            'boolean true value' => [
                'MockCollection',
                'flag',
                true,
                'flag',
                'boolean'
            ],
            'boolean false value' => [
                'MockCollection',
                'disabled',
                false,
                'disabled',
                'boolean'
            ],
            'float value' => [
                'MockCollection',
                'pi',
                3.14159,
                'pi',
                'double'
            ],
            'array value' => [
                'MockCollection',
                'data',
                ['nested' => 'array'],
                'data',
                'array'
            ],

            // Complex offset types
            'array offset' => [
                'MockCollection',
                ['complex', 'offset'],
                'value',
                'array',
                'string'
            ],
            'object offset' => [
                'MockCollection',
                new \stdClass(),
                'value',
                'object',
                'string'
            ],
            'null offset' => [
                'MockCollection',
                null,
                'value',
                'NULL',
                'string'
            ],
            'boolean offset' => [
                'MockCollection',
                true,
                'value',
                'boolean',
                'string'
            ],
            'resource offset' => [
                'MockCollection',
                $resource,
                'value',
                'resource (closed)',
                'string'
            ],

            // Edge cases
            'empty string offset' => [
                'MockCollection',
                '',
                'value',
                '',
                'string'
            ],
            'empty string value' => [
                'MockCollection',
                'key',
                '',
                'key',
                'string'
            ],
            'zero offset' => [
                'MockCollection',
                0,
                'value',
                '0',
                'string'
            ],
            'negative offset' => [
                'MockCollection',
                -1,
                'value',
                '-1',
                'string'
            ],

            // Special numeric values
            'float offset' => [
                'MockCollection',
                3.14,
                'value',
                '3.14',
                'string'
            ],
            'large integer offset' => [
                'MockCollection',
                \PHP_INT_MAX,
                'value',
                (string)\PHP_INT_MAX,
                'string'
            ],
        ];
    }
}
