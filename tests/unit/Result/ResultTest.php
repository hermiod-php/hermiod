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
        $json = [];

        $result = new Result(
            $this->mockReflector(),
            $this->mockHydrator(),
            $json,
        );

        $this->assertInstanceOf(
            ResultInterface::class,
            $result,
            'Result should implement ResultInterface'
        );
    }

    public function testIsValid(): void
    {
        $json = [];
        $validation = $this->mockValidation(true);
        $reflector = $this->mockReflector($validation);
        $result = new Result($reflector, $this->mockHydrator(), $json);

        $this->assertTrue(
            $result->isValid(),
            'isValid() should return true if validation result is valid'
        );
    }

    public function testGetErrors(): void
    {
        $json = [];
        $validation = $this->mockValidation();
        $reflector = $this->mockReflector($validation);
        $result = new Result($reflector, $this->mockHydrator(), $json);

        $this->assertInstanceOf(
            CollectionInterface::class,
            $result->getErrors(),
            'getErrors() should return an instance of CollectionInterface'
        );
    }

    public function testInstanceThrowsOnInvalid(): void
    {
        $json = [];
        $validation = $this->mockValidation(false);
        $reflector = $this->mockReflector($validation);
        $result = new Result($reflector, $this->mockHydrator(), $json);

        $this->expectException(InvalidJsonPayloadException::class);

        $result->getInstance();
    }

    public function testInstanceReturnsHydratedObject(): void
    {
        $json = [];
        $object = new \stdClass();
        $validation = $this->mockValidation(true);
        $reflector = $this->mockReflector($validation);
        $hydrator = $this->mockHydrator($object);

        $result = new Result($reflector, $hydrator, $json);

        $this->assertSame(
            $object,
            $result->getInstance(),
            'instance() should return the hydrated object'
        );
    }

    private function mockReflector(?ValidationResultInterface $validation = null): ResourceInterface & \PHPUnit\Framework\MockObject\MockObject
    {
        $reflector = $this->createMock(ResourceInterface::class);
        $reflector->method('validateAndTranspose')->willReturn($validation ?? $this->mockValidation());

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
