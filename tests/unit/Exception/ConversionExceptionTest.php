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
}
