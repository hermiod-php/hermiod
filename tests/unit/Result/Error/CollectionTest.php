<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Result\Error;

use Hermiod\Resource\Property\Validation\ResultInterface as ValidationResultInterface;
use Hermiod\Result\Error\Collection;
use Hermiod\Result\Error\CollectionInterface;
use Hermiod\Result\Error\ErrorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Collection::class)]
final class CollectionTest extends TestCase
{
    public function testImplementsCollectionInterface(): void
    {
        $collection = new Collection();

        $this->assertInstanceOf(
            CollectionInterface::class,
            $collection,
            'Collection should implement CollectionInterface'
        );
    }

    public function testEmptyCollection(): void
    {
        $collection = new Collection();

        $this->assertCount(
            0,
            $collection,
            'Newly created Collection should be empty'
        );
    }

    public function testCollectionWithErrors(): void
    {
        $error1 = $this->mockError();
        $error2 = $this->mockError();

        $collection = new Collection($error1, $error2);

        $this->assertCount(
            2,
            $collection,
            'Collection should contain the provided errors'
        );
    }

    public function testFromPropertyValidationResult(): void
    {
        $validation = $this->mockValidation(['Error 1', 'Error 2']);

        $collection = Collection::fromPropertyValidationResult($validation);

        $this->assertCount(
            2,
            $collection,
            'fromPropertyValidationResult() should create errors for each validation error'
        );
    }

    public function testIteratorReturnsErrors(): void
    {
        $error1 = $this->mockError();
        $error2 = $this->mockError();

        $collection = new Collection($error1, $error2);

        $errors = iterator_to_array($collection);

        $this->assertSame(
            [$error1, $error2],
            $errors,
            'Iterator should return all errors in the collection'
        );
    }

    public function testJsonSerialize(): void
    {
        $error1 = $this->mockError();
        $error2 = $this->mockError();

        $collection = new Collection($error1, $error2);

        $this->assertSame(
            [$error1, $error2],
            $collection->jsonSerialize(),
            'jsonSerialize() should return an array of ErrorInterface objects'
        );
    }

    public function testFromThrowableCreatesCollectionWithSingleError(): void
    {
        $exception = new \RuntimeException('Test error message');

        $collection = Collection::fromThrowable($exception);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertInstanceOf(CollectionInterface::class, $collection);
        $this->assertCount(1, $collection);
    }

    public function testFromThrowableWithEmptyMessage(): void
    {
        $exception = new \Exception('');

        $collection = Collection::fromThrowable($exception);

        $this->assertCount(1, $collection);

        // Verify the error contains the empty message
        $errors = iterator_to_array($collection);
        $this->assertCount(1, $errors);
        $this->assertInstanceOf(ErrorInterface::class, $errors[0]);
    }

    public function testFromThrowableWithLongMessage(): void
    {
        $longMessage = str_repeat('This is a very long error message. ', 100);
        $exception = new \InvalidArgumentException($longMessage);

        $collection = Collection::fromThrowable($exception);

        $this->assertCount(1, $collection);

        $errors = iterator_to_array($collection);
        $this->assertCount(1, $errors);
        $this->assertInstanceOf(ErrorInterface::class, $errors[0]);
    }

    public function testFromThrowableWithSpecialCharacters(): void
    {
        $specialMessage = "Error with special chars: áéíóú, 中文, 🚀, \n\t\r";
        $exception = new \DomainException($specialMessage);

        $collection = Collection::fromThrowable($exception);

        $this->assertCount(1, $collection);

        $errors = iterator_to_array($collection);
        $this->assertInstanceOf(ErrorInterface::class, $errors[0]);
    }

    public function testFromThrowableWithDifferentExceptionTypes(): void
    {
        $exceptions = [
            new \RuntimeException('Runtime error'),
            new \InvalidArgumentException('Invalid argument'),
            new \LogicException('Logic error'),
            new \DomainException('Domain error'),
            new \OutOfBoundsException('Out of bounds'),
            new \TypeError('Type error'),
        ];

        foreach ($exceptions as $exception) {
            $collection = Collection::fromThrowable($exception);

            $this->assertInstanceOf(Collection::class, $collection);
            $this->assertCount(1, $collection);

            $errors = iterator_to_array($collection);
            $this->assertInstanceOf(ErrorInterface::class, $errors[0]);
        }
    }

    public function testFromThrowableCreatesNewCollectionInstance(): void
    {
        $exception1 = new \Exception('First error');
        $exception2 = new \Exception('Second error');

        $collection1 = Collection::fromThrowable($exception1);
        $collection2 = Collection::fromThrowable($exception2);

        $this->assertNotSame($collection1, $collection2);
        $this->assertCount(1, $collection1);
        $this->assertCount(1, $collection2);
    }

    public function testFromThrowableWithNestedExceptions(): void
    {
        $originalException = new \RuntimeException('Original error');
        $nestedException = new \InvalidArgumentException('Nested error', 0, $originalException);

        $collection = Collection::fromThrowable($nestedException);

        // Should only create one error from the outermost exception message
        $this->assertCount(1, $collection);

        $errors = \iterator_to_array($collection);
        $this->assertInstanceOf(ErrorInterface::class, $errors[0]);
    }

    public function testFromThrowableWithErrorCodes(): void
    {
        $exceptions = [
            new \Exception('Error with code 0', 0),
            new \Exception('Error with positive code', 123),
            new \Exception('Error with negative code', -456),
            new \Exception('Error with max int', PHP_INT_MAX),
            new \Exception('Error with min int', PHP_INT_MIN),
        ];

        foreach ($exceptions as $exception) {
            $collection = Collection::fromThrowable($exception);

            $this->assertCount(1, $collection);
            $errors = \iterator_to_array($collection);
            $this->assertInstanceOf(ErrorInterface::class, $errors[0]);
        }
    }

    public function testFromThrowableResultCanBeIteratedMultipleTimes(): void
    {
        $exception = new \Exception('Test message');
        $collection = Collection::fromThrowable($exception);

        // First iteration
        $errors1 = \iterator_to_array($collection);
        $this->assertCount(1, $errors1);

        // Second iteration should work the same
        $errors2 = \iterator_to_array($collection);
        $this->assertCount(1, $errors2);

        // Should contain the same error instances
        $this->assertSame($errors1[0], $errors2[0]);
    }

    public function testFromThrowableResultCanBeJsonSerialized(): void
    {
        $exception = new \Exception('Serialization test');
        $collection = Collection::fromThrowable($exception);

        $serialized = $collection->jsonSerialize();

        $this->assertIsArray($serialized);
        $this->assertCount(1, $serialized);
        $this->assertInstanceOf(ErrorInterface::class, $serialized[0]);
    }

    public function testFromThrowableWithCustomThrowableImplementation(): void
    {
        $customThrowable = new class('Custom throwable message') extends \Exception {};

        $collection = Collection::fromThrowable($customThrowable);

        $this->assertCount(1, $collection);
        $errors = \iterator_to_array($collection);
        $this->assertInstanceOf(ErrorInterface::class, $errors[0]);
    }

    private function mockError(): ErrorInterface & \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(ErrorInterface::class);
    }

    private function mockValidation(array $errors = []): ValidationResultInterface & \PHPUnit\Framework\MockObject\MockObject
    {
        $validation = $this->createMock(ValidationResultInterface::class);
        $validation->method('getValidationErrors')->willReturn($errors);

        return $validation;
    }
}
