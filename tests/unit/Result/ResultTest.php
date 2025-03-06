<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Result;

use Hermiod\Resource\Hydrator\HydratorInterface;
use Hermiod\Resource\Property\Validation\ResultInterface as ValidationResultInterface;
use Hermiod\Resource\ResourceInterface;
use Hermiod\Result\Error\CollectionInterface;
use Hermiod\Result\Exception\InvalidJsonPayloadException;
use Hermiod\Result\Result;
use Hermiod\Result\ResultInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Result::class)]
final class ResultTest extends TestCase
{
    public function testImplementsResultInterface(): void
    {
        $result = new Result(
            $this->mockReflector(),
            $this->mockHydrator(),
            []
        );

        $this->assertInstanceOf(
            ResultInterface::class,
            $result,
            'Result should implement ResultInterface'
        );
    }

    public function testIsValid(): void
    {
        $validation = $this->mockValidation(true);
        $reflector = $this->mockReflector($validation);
        $result = new Result($reflector, $this->mockHydrator(), []);

        $this->assertTrue(
            $result->isValid(),
            'isValid() should return true if validation result is valid'
        );
    }

    public function testGetErrors(): void
    {
        $validation = $this->mockValidation();
        $reflector = $this->mockReflector($validation);
        $result = new Result($reflector, $this->mockHydrator(), []);

        $this->assertInstanceOf(
            CollectionInterface::class,
            $result->getErrors(),
            'getErrors() should return an instance of CollectionInterface'
        );
    }

    public function testInstanceThrowsOnInvalid(): void
    {
        $validation = $this->mockValidation(false);
        $reflector = $this->mockReflector($validation);
        $result = new Result($reflector, $this->mockHydrator(), []);

        $this->expectException(InvalidJsonPayloadException::class);

        $result->getInstance();
    }

    public function testInstanceReturnsHydratedObject(): void
    {
        $object = new \stdClass();
        $validation = $this->mockValidation(true);
        $reflector = $this->mockReflector($validation);
        $hydrator = $this->mockHydrator($object);

        $result = new Result($reflector, $hydrator, []);

        $this->assertSame(
            $object,
            $result->getInstance(),
            'instance() should return the hydrated object'
        );
    }

    private function mockReflector(?ValidationResultInterface $validation = null): ResourceInterface & \PHPUnit\Framework\MockObject\MockObject
    {
        $reflector = $this->createMock(ResourceInterface::class);
        $reflector->method('validate')->willReturn($validation ?? $this->mockValidation());

        return $reflector;
    }

    private function mockHydrator(?object $object = null): HydratorInterface & \PHPUnit\Framework\MockObject\MockObject
    {
        $hydrator = $this->createMock(HydratorInterface::class);
        $hydrator->method('hydrate')->willReturn($object ?? new \stdClass());

        return $hydrator;
    }

    private function mockValidation(bool $isValid = false): ValidationResultInterface & \PHPUnit\Framework\MockObject\MockObject
    {
        $validation = $this->createMock(ValidationResultInterface::class);
        $validation->method('isValid')->willReturn($isValid);

        return $validation;
    }
}
