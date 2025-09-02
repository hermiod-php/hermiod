<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\Exception\PropertyClassTypeNotFoundException;
use Hermiod\Resource\Property\FactoryInterface;
use Hermiod\Resource\Property\InterfaceProperty;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\CollectionInterface;
use Hermiod\Resource\Property\Traits\ConstructWithNameAndNullableTrait;
use Hermiod\Resource\Property\Traits\GetPropertyNameTrait;
use Hermiod\Resource\Property\Validation\Result;
use Hermiod\Resource\Property\Validation\ResultInterface;
use Hermiod\Resource\ResourceInterface;
use Hermiod\Resource\RuntimeResolverInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(InterfaceProperty::class)]
#[CoversClass(ConstructWithNameAndNullableTrait::class)]
#[CoversClass(GetPropertyNameTrait::class)]
class InterfacePropertyTest extends TestCase
{
    private FactoryInterface|MockObject $factory;

    protected function setUp(): void
    {
        $this->factory = $this->createFactoryMock();
    }

    public function testImplementsPropertyInterface(): void
    {
        $property = new InterfaceProperty('test', \JsonSerializable::class, false, $this->factory);

        $this->assertInstanceOf(PropertyInterface::class, $property);
    }

    public function testImplementsRuntimeResolverInterface(): void
    {
        $property = new InterfaceProperty('test', \JsonSerializable::class, false, $this->factory);

        $this->assertInstanceOf(RuntimeResolverInterface::class, $property);
    }

    public function testGetPropertyName(): void
    {
        $property = new InterfaceProperty('testProperty', \JsonSerializable::class, false, $this->factory);

        $this->assertSame('testProperty', $property->getPropertyName());
    }

    public function testIsNullableWhenTrue(): void
    {
        $property = new InterfaceProperty('test', \JsonSerializable::class, true, $this->factory);

        $this->assertTrue($property->isNullable());
    }

    public function testIsNullableWhenFalse(): void
    {
        $property = new InterfaceProperty('test', \JsonSerializable::class, false, $this->factory);

        $this->assertFalse($property->isNullable());
    }

    public function testGetDefaultValueReturnsNull(): void
    {
        $property = new InterfaceProperty('test', \JsonSerializable::class, false, $this->factory);

        $this->assertNull($property->getDefaultValue());
    }

    public function testHasDefaultValueWhenNoDefault(): void
    {
        $property = new InterfaceProperty('test', \JsonSerializable::class, false, $this->factory);

        $this->assertFalse($property->hasDefaultValue());
    }

    public function testHasDefaultValueWhenWithDefaultNullValue(): void
    {
        $property = InterfaceProperty::withDefaultNullValue('test', \JsonSerializable::class, true, $this->factory);

        $this->assertTrue($property->hasDefaultValue());
    }

    public function testWithDefaultNullValueCreatesNewInstance(): void
    {
        $property = InterfaceProperty::withDefaultNullValue('test', \JsonSerializable::class, true, $this->factory);

        $this->assertInstanceOf(InterfaceProperty::class, $property);
        $this->assertTrue($property->hasDefaultValue());
        $this->assertSame('test', $property->getPropertyName());
        $this->assertTrue($property->isNullable());
    }

    public function testConstructorThrowsExceptionForNonExistentInterface(): void
    {
        $this->expectException(PropertyClassTypeNotFoundException::class);

        new InterfaceProperty('test', 'NonExistentInterface', false, $this->factory);
    }

    public function testConstructorThrowsExceptionForNonInterface(): void
    {
        $this->expectException(PropertyClassTypeNotFoundException::class);

        new InterfaceProperty('test', \stdClass::class, false, $this->factory);
    }

    public function testNormaliseJsonValueWithNonObject(): void
    {
        $property = new InterfaceProperty('test', \JsonSerializable::class, false, $this->factory);

        $this->assertNull($property->normaliseJsonValue('string'));
        $this->assertNull($property->normaliseJsonValue(123));
        $this->assertNull($property->normaliseJsonValue([]));
        $this->assertNull($property->normaliseJsonValue(true));
        $this->assertNull($property->normaliseJsonValue(null));
    }

    public function testNormaliseJsonValueWithObjectNotImplementingInterface(): void
    {
        $property = new InterfaceProperty('test', \JsonSerializable::class, false, $this->factory);

        $this->assertNull($property->normaliseJsonValue(new \stdClass()));
        $this->assertNull($property->normaliseJsonValue(new \DateTime()));
    }

    public function testNormaliseJsonValueWithJsonSerializableObject(): void
    {
        $serializable = new class implements \JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return ['key' => 'value'];
            }
        };

        $property = new InterfaceProperty('test', \JsonSerializable::class, false, $this->factory);

        $result = $property->normaliseJsonValue($serializable);

        $this->assertSame(['key' => 'value'], $result);
    }

    public function testNormaliseJsonValueWithNonJsonSerializableInterfaceImplementorWhenCannotAutoSerialise(): void
    {
        $object = new class implements \Iterator {
            public function current(): mixed { return null; }
            public function next(): void {}
            public function key(): mixed { return null; }
            public function valid(): bool { return false; }
            public function rewind(): void {}
        };

        $classProperty = $this->createCustomIntersectionTypeMock(false);

        $this->factory
            ->expects($this->once())
            ->method('createClassProperty')
            ->with('test', \get_class($object), false, false)
            ->willReturn($classProperty);

        $property = new InterfaceProperty('test', \Iterator::class, false, $this->factory);

        $result = $property->normaliseJsonValue($object);

        $this->assertNull($result);
    }

    public function testNormaliseJsonValueWithNonJsonSerializableInterfaceImplementorWhenCanAutoSerialise(): void
    {
        $object = new class implements \Iterator {
            public string $name = 'test';
            public int $value = 42;

            public function current(): mixed { return null; }
            public function next(): void {}
            public function key(): mixed { return null; }
            public function valid(): bool { return false; }
            public function rewind(): void {}
        };

        $property1 = $this->createPropertyMock();
        $property1->method('getPropertyName')->willReturn('name');
        $property1->method('normaliseJsonValue')->willReturn('test');

        $property2 = $this->createPropertyMock();
        $property2->method('getPropertyName')->willReturn('value');
        $property2->method('normaliseJsonValue')->willReturn(42);

        $collection = $this->createCollectionMock();
        $collection->method('rewind');
        $collection->method('valid')->willReturnOnConsecutiveCalls(true, true, false);
        $collection->method('current')->willReturnOnConsecutiveCalls($property1, $property2);
        $collection->method('next');

        $classProperty = $this->createIntersectionTypeMock();
        // Since we're returning a concrete class, we need to modify its behavior differently
        $classProperty = $this->createCustomIntersectionTypeMock(true, $collection);

        $this->factory
            ->expects($this->once())
            ->method('createClassProperty')
            ->with('test', \get_class($object), false, false)
            ->willReturn($classProperty);

        $property = new InterfaceProperty('test', \Iterator::class, false, $this->factory);

        $result = $property->normaliseJsonValue($object);

        $expected = (object) ['name' => 'test', 'value' => 42];
        $this->assertEquals($expected, $result);
    }

    public function testCheckValueAgainstConstraintsWithNullAndNullable(): void
    {
        $property = new InterfaceProperty('test', \JsonSerializable::class, true, $this->factory);
        $path = $this->createPathMock();

        $result = $property->checkValueAgainstConstraints($path, null);

        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertTrue($result->isValid());
    }

    public function testCheckValueAgainstConstraintsWithArrayValue(): void
    {
        $testArray = ['key' => 'value'];
        $path = $this->createPathMock();
        $validationResult = $this->createValidationResultMock();

        $concreteResource = $this->createCustomIntersectionTypeMock(false, null, $validationResult);

        $this->factory
            ->expects($this->once())
            ->method('createClassPropertyForInterfaceGivenFragment')
            ->with('test', \JsonSerializable::class, false, false, $testArray)
            ->willReturn($concreteResource);

        $property = new InterfaceProperty('test', \JsonSerializable::class, false, $this->factory);

        $result = $property->checkValueAgainstConstraints($path, $testArray);

        $this->assertSame($validationResult, $result);
    }

    public function testCheckValueAgainstConstraintsWithObjectValue(): void
    {
        $testObject = (object) ['key' => 'value'];
        $path = $this->createPathMock();
        $validationResult = $this->createValidationResultMock();

        $concreteResource = $this->createCustomIntersectionTypeMock(false, null, $validationResult);

        $this->factory
            ->expects($this->once())
            ->method('createClassPropertyForInterfaceGivenFragment')
            ->with('test', \JsonSerializable::class, false, false, ['key' => 'value'])
            ->willReturn($concreteResource);

        $property = new InterfaceProperty('test', \JsonSerializable::class, false, $this->factory);

        $result = $property->checkValueAgainstConstraints($path, $testObject);

        $this->assertSame($validationResult, $result);
    }

    public function testCheckValueAgainstConstraintsWithNonIterableValue(): void
    {
        $property = new InterfaceProperty('test', \JsonSerializable::class, false, $this->factory);
        $path = $this->createPathMock();

        $result = $property->checkValueAgainstConstraints($path, 'string');

        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertFalse($result->isValid());
        $this->assertContains('Must be iterable', $result->getValidationErrors());
    }

    public function testCheckValueAgainstConstraintsWithNullAndNonNullable(): void
    {
        $property = new InterfaceProperty('test', \JsonSerializable::class, false, $this->factory);
        $path = $this->createPathMock();

        $result = $property->checkValueAgainstConstraints($path, null);

        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertFalse($result->isValid());
        $this->assertContains('Must be iterable', $result->getValidationErrors());
    }

    public function testGetConcreteResource(): void
    {
        $fragment = ['type' => 'test'];
        $expectedResource = $this->createIntersectionTypeMock();

        $this->factory
            ->expects($this->once())
            ->method('createClassPropertyForInterfaceGivenFragment')
            ->with('test', \JsonSerializable::class, true, true, $fragment)
            ->willReturn($expectedResource);

        $property = InterfaceProperty::withDefaultNullValue('test', \JsonSerializable::class, true, $this->factory);

        $result = $property->getConcreteResource($fragment);

        $this->assertSame($expectedResource, $result);
    }

    public function testGetConcreteResourceWithEmptyFragment(): void
    {
        $fragment = [];
        $expectedResource = $this->createIntersectionTypeMock();

        $this->factory
            ->expects($this->once())
            ->method('createClassPropertyForInterfaceGivenFragment')
            ->with('test', \JsonSerializable::class, false, false, $fragment)
            ->willReturn($expectedResource);

        $property = new InterfaceProperty('test', \JsonSerializable::class, false, $this->factory);

        $result = $property->getConcreteResource($fragment);

        $this->assertSame($expectedResource, $result);
    }

    private function createFactoryMock(): FactoryInterface|MockObject
    {
        return $this->createMock(FactoryInterface::class);
    }

    private function createResourceMock(): ResourceInterface|MockObject
    {
        return $this->createMock(ResourceInterface::class);
    }

    private function createPropertyMock(): PropertyInterface|MockObject
    {
        return $this->createMock(PropertyInterface::class);
    }

    private function createPathMock(): PathInterface|MockObject
    {
        return $this->createMock(PathInterface::class);
    }

    private function createCollectionMock(): CollectionInterface|MockObject
    {
        return $this->createMock(CollectionInterface::class);
    }

    private function createValidationResultMock(): ResultInterface|MockObject
    {
        return $this->createMock(ResultInterface::class);
    }

    private function createIntersectionTypeMock(): object
    {
        // Create a concrete test double class that implements all required interfaces
        $testDoubleClass = new class implements ResourceInterface, PropertyInterface {
            public function canAutomaticallySerialise(): bool { return false; }
            public function getClassName(): string { return 'TestClass'; }
            public function validateAndTranspose(PathInterface $path, object|array &$json): ResultInterface {
                return new Result();
            }
            public function getProperties(): CollectionInterface {
                // Return a simple mock collection
                $mock = new class implements CollectionInterface {
                    public function offsetExists(mixed $offset): bool { return false; }
                    public function offsetGet(mixed $offset): ?PropertyInterface { return null; }
                    public function offsetSet(mixed $offset, mixed $value): void {}
                    public function offsetUnset(mixed $offset): void {}
                    public function getIterator(): \Traversable { return new \EmptyIterator(); }
                    public function count(): int { return 0; }
                    public function current(): ?PropertyInterface { return null; }
                    public function next(): void { }
                    public function key(): mixed { return null; }
                    public function valid(): bool { return false; }
                    public function rewind(): void { }
                };
                return $mock;
            }
            public function getPropertyName(): string { return 'test'; }
            public function isNullable(): bool { return false; }
            public function getDefaultValue(): mixed { return null; }
            public function hasDefaultValue(): bool { return false; }
            public function normaliseJsonValue(mixed $value): mixed { return null; }
            public function checkValueAgainstConstraints(PathInterface $path, mixed $value): ResultInterface {
                return new Result();
            }
        };

        // Return the actual instance, not a mock of it, since it already implements all needed methods
        return $testDoubleClass;
    }

    private function createMockImplementingMultipleInterfaces(array $interfaces): object
    {
        return $this->createIntersectionTypeMock();
    }

    private function createCustomIntersectionTypeMock(
        bool $canAutoSerialise = false,
        ?CollectionInterface $properties = null,
        ?ResultInterface $validationResult = null
    ): object {
        return new class($canAutoSerialise, $properties, $validationResult) implements ResourceInterface, PropertyInterface {
            public function __construct(
                private bool $canAutoSerialise,
                private ?CollectionInterface $properties,
                private ?ResultInterface $validationResult
            ) {}

            public function canAutomaticallySerialise(): bool {
                return $this->canAutoSerialise;
            }
            public function getClassName(): string { return 'TestClass'; }
            public function validateAndTranspose(PathInterface $path, object|array &$json): ResultInterface {
                return $this->validationResult ?? new Result();
            }
            public function getProperties(): CollectionInterface {
                return $this->properties ?? new class implements CollectionInterface {
                    public function offsetExists(mixed $offset): bool { return false; }
                    public function offsetGet(mixed $offset): ?PropertyInterface { return null; }
                    public function offsetSet(mixed $offset, mixed $value): void {}
                    public function offsetUnset(mixed $offset): void {}
                    public function getIterator(): \Traversable { return new \EmptyIterator(); }
                    public function count(): int { return 0; }
                    public function current(): ?PropertyInterface { return null; }
                    public function next(): void { }
                    public function key(): mixed { return null; }
                    public function valid(): bool { return false; }
                    public function rewind(): void { }
                };
            }
            public function getPropertyName(): string { return 'test'; }
            public function isNullable(): bool { return false; }
            public function getDefaultValue(): mixed { return null; }
            public function hasDefaultValue(): bool { return false; }
            public function normaliseJsonValue(mixed $value): mixed { return null; }
            public function checkValueAgainstConstraints(PathInterface $path, mixed $value): ResultInterface {
                return new Result();
            }
        };
    }
}
