<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Result\Error;

use Hermiod\Result\Error\Collection;
use Hermiod\Result\Error\CollectionInterface;
use Hermiod\Result\Error\ErrorInterface;
use Hermiod\Resource\Reflector\Property\Validation\ResultInterface as ValidationResultInterface;
use PHPUnit\Framework\TestCase;

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
