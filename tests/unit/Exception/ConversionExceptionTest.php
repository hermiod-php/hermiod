<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Exception;

use Hermiod\Exception\ConversionException;
use Hermiod\Exception\Exception;
use Hermiod\Result\Error\CollectionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConversionException::class)]
final class ConversionExceptionTest extends TestCase
{
    public function testDueToTranspositionErrorsWithSingleError(): void
    {
        $errors = $this->createMock(CollectionInterface::class);
        $errors->method('count')->willReturn(1);

        $exception = ConversionException::dueToTranspositionErrors($errors);

        $this->assertInstanceOf(ConversionException::class, $exception);
        $this->assertInstanceOf(\DomainException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertSame('Invalid property in JSON structure. ', $exception->getMessage());
        $this->assertSame($errors, $exception->getErrors());
    }

    public function testDueToTranspositionErrorsWithMultipleErrors(): void
    {
        $errors = $this->createMock(CollectionInterface::class);
        $errors->method('count')->willReturn(3);

        $exception = ConversionException::dueToTranspositionErrors($errors);

        $this->assertInstanceOf(ConversionException::class, $exception);
        $this->assertInstanceOf(\DomainException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertSame('Invalid properties in JSON structure. ', $exception->getMessage());
        $this->assertSame($errors, $exception->getErrors());
    }

    public function testDueToTranspositionErrorsWithZeroErrors(): void
    {
        $errors = $this->createMock(CollectionInterface::class);
        $errors->method('count')->willReturn(0);

        $exception = ConversionException::dueToTranspositionErrors($errors);

        $this->assertSame('Invalid properties in JSON structure. ', $exception->getMessage());
        $this->assertSame($errors, $exception->getErrors());
    }

    public function testDueToTranspositionErrorsWithTwoErrors(): void
    {
        $errors = $this->createMock(CollectionInterface::class);
        $errors->method('count')->willReturn(2);

        $exception = ConversionException::dueToTranspositionErrors($errors);

        $this->assertSame('Invalid properties in JSON structure. ', $exception->getMessage());
        $this->assertSame($errors, $exception->getErrors());
    }

    public function testGetErrorsReturnsOriginalCollection(): void
    {
        $errors = $this->createMock(CollectionInterface::class);
        $errors->method('count')->willReturn(1);

        $exception = ConversionException::dueToTranspositionErrors($errors);

        $this->assertSame($errors, $exception->getErrors());
    }

    public function testExceptionHierarchy(): void
    {
        $errors = $this->createMock(CollectionInterface::class);
        $errors->method('count')->willReturn(1);

        $exception = ConversionException::dueToTranspositionErrors($errors);

        $this->assertInstanceOf(\DomainException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testSingularVsPluralMessage(): void
    {
        $singleError = $this->createMock(CollectionInterface::class);
        $singleError->method('count')->willReturn(1);

        $multipleErrors = $this->createMock(CollectionInterface::class);
        $multipleErrors->method('count')->willReturn(5);

        $singleException = ConversionException::dueToTranspositionErrors($singleError);
        $multipleException = ConversionException::dueToTranspositionErrors($multipleErrors);

        $this->assertStringContainsString('property', $singleException->getMessage());
        $this->assertStringNotContainsString('properties', $singleException->getMessage());

        $this->assertStringContainsString('properties', $multipleException->getMessage());
        $this->assertStringNotContainsString('property in', $multipleException->getMessage());
    }

    public function testLargeNumberOfErrors(): void
    {
        $errors = $this->createMock(CollectionInterface::class);
        $errors->method('count')->willReturn(100);

        $exception = ConversionException::dueToTranspositionErrors($errors);

        $this->assertSame('Invalid properties in JSON structure. ', $exception->getMessage());
        $this->assertSame($errors, $exception->getErrors());
    }

    public function testDueToUnparsableJsonExceptionCreatesNewInstance(): void
    {
        $previousException = $this->createExceptionImplementation('JSON parsing failed', 123);

        $exception = ConversionException::dueToUnparsableJsonException($previousException);

        $this->assertInstanceOf(ConversionException::class, $exception);
        $this->assertInstanceOf(\DomainException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertSame('JSON parsing failed', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function testDueToUnparsableJsonExceptionWithEmptyMessage(): void
    {
        $previousException = $this->createExceptionImplementation('', 0);

        $exception = ConversionException::dueToUnparsableJsonException($previousException);

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function testDueToUnparsableJsonExceptionWithNegativeCode(): void
    {
        $previousException = $this->createExceptionImplementation('Negative code test', -456);

        $exception = ConversionException::dueToUnparsableJsonException($previousException);

        $this->assertSame('Negative code test', $exception->getMessage());
        $this->assertSame(-456, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function testDueToUnparsableJsonExceptionWithLongMessage(): void
    {
        $longMessage = \str_repeat('This is a very long error message. ', 100);
        $previousException = $this->createExceptionImplementation($longMessage, 789);

        $exception = ConversionException::dueToUnparsableJsonException($previousException);

        $this->assertSame($longMessage, $exception->getMessage());
        $this->assertSame(789, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function testDueToUnparsableJsonExceptionSetsErrorsFromThrowable(): void
    {
        $previousException = $this->createExceptionImplementation('Test message', 0);

        $exception = ConversionException::dueToUnparsableJsonException($previousException);

        $errors = $exception->getErrors();

        $this->assertInstanceOf(CollectionInterface::class, $errors);

        // The errors should be created from Collection::fromThrowable()
        // We can't easily test the internal state of Collection::fromThrowable without knowing its implementation,
        // but we can verify that getErrors() returns a CollectionInterface
        $this->assertNotNull($errors);
    }

    public function testDueToUnparsableJsonExceptionPreservesExceptionChain(): void
    {
        $originalException = new \RuntimeException('Original error', 100);

        $mockException = $this->createExceptionImplementation('Final error', 300);

        $exception = ConversionException::dueToUnparsableJsonException($mockException);

        $this->assertSame($mockException, $exception->getPrevious());
        $this->assertSame('Final error', $exception->getMessage());
        $this->assertSame(300, $exception->getCode());
    }

    public function testDueToUnparsableJsonExceptionWithSpecialCharactersInMessage(): void
    {
        $specialMessage = "Error with special chars: áéíóú, 中文, 🚀, \n\t\r";
        $previousException = $this->createExceptionImplementation($specialMessage, 42);

        $exception = ConversionException::dueToUnparsableJsonException($previousException);

        $this->assertSame($specialMessage, $exception->getMessage());
        $this->assertSame(42, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function testDueToUnparsableJsonExceptionWithMaxIntCode(): void
    {
        $previousException = $this->createExceptionImplementation('Max int test', PHP_INT_MAX);

        $exception = ConversionException::dueToUnparsableJsonException($previousException);

        $this->assertSame('Max int test', $exception->getMessage());
        $this->assertSame(PHP_INT_MAX, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function testDueToUnparsableJsonExceptionWithMinIntCode(): void
    {
        $previousException = $this->createExceptionImplementation('Min int test', PHP_INT_MIN);

        $exception = ConversionException::dueToUnparsableJsonException($previousException);

        $this->assertSame('Min int test', $exception->getMessage());
        $this->assertSame(PHP_INT_MIN, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }

    private function createExceptionImplementation(string $message, int $code): Exception
    {
        return new class($message, $code) extends \Exception implements Exception {};
    }
}
