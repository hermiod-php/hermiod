<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property;

use Hermiod\Resource\FactoryInterface;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\ClassProperty;
use Hermiod\Resource\Property\CollectionInterface;
use Hermiod\Resource\Property\Exception\PropertyClassTypeNotFoundException;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\Validation\ResultInterface;
use Hermiod\Resource\ResourceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClassProperty::class)]
class ClassPropertyTest extends TestCase
{
    private FactoryInterface $factory;

    protected function setUp(): void
    {
        $this->factory = $this->createFactoryMock();
    }

    public function testImplementsPropertyInterface(): void
    {
        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $this->assertInstanceOf(PropertyInterface::class, $property);
    }

    public function testImplementsResourceInterface(): void
    {
        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $this->assertInstanceOf(ResourceInterface::class, $property);
    }

    public function testGetPropertyName(): void
    {
        $property = new ClassProperty('testProperty', \stdClass::class, false, $this->factory);

        $this->assertSame('testProperty', $property->getPropertyName());
    }

    public function testGetClassName(): void
    {
        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $this->assertSame(\stdClass::class, $property->getClassName());
    }

    public function testIsNullableWhenTrue(): void
    {
        $property = new ClassProperty('test', \stdClass::class, true, $this->factory);

        $this->assertTrue($property->isNullable());
    }

    public function testIsNullableWhenFalse(): void
    {
        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $this->assertFalse($property->isNullable());
    }

    public function testGetDefaultValueReturnsNull(): void
    {
        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $this->assertNull($property->getDefaultValue());
    }

    public function testHasDefaultValueWhenNoDefault(): void
    {
        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $this->assertFalse($property->hasDefaultValue());
    }

    public function testHasDefaultValueWhenWithDefaultNullValue(): void
    {
        $property = ClassProperty::withDefaultNullValue('test', \stdClass::class, true, $this->factory);

        $this->assertTrue($property->hasDefaultValue());
    }

    public function testWithDefaultNullValueCreatesNewInstance(): void
    {
        $property = ClassProperty::withDefaultNullValue('test', \stdClass::class, true, $this->factory);

        $this->assertInstanceOf(ClassProperty::class, $property);
        $this->assertTrue($property->hasDefaultValue());
        $this->assertSame('test', $property->getPropertyName());
        $this->assertSame(\stdClass::class, $property->getClassName());
        $this->assertTrue($property->isNullable());
    }

    public function testConstructorThrowsExceptionForNonExistentClass(): void
    {
        $this->expectException(PropertyClassTypeNotFoundException::class);

        new ClassProperty('test', 'NonExistentClass', false, $this->factory);
    }

    public function testNormaliseJsonValueWithNonObject(): void
    {
        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $this->assertNull($property->normaliseJsonValue('string'));
        $this->assertNull($property->normaliseJsonValue(123));
        $this->assertNull($property->normaliseJsonValue([]));
        $this->assertNull($property->normaliseJsonValue(true));
    }

    public function testNormaliseJsonValueWithWrongObjectType(): void
    {
        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $this->assertNull($property->normaliseJsonValue(new \DateTime()));
    }

    public function testNormaliseJsonValueWithJsonSerializableObject(): void
    {
        // Create a concrete class that implements JsonSerializable for testing
        $serializable = new class implements \JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return ['key' => 'value'];
            }
        };

        $property = new ClassProperty('test', \get_class($serializable), false, $this->factory);

        $result = $property->normaliseJsonValue($serializable);

        $this->assertSame(['key' => 'value'], $result);
    }

    public function testNormaliseJsonValueWithNonJsonSerializableObjectWhenCannotAutoSerialise(): void
    {
        $object = new \stdClass();

        $resource = $this->createResourceMock();
        $resource->method('canAutomaticallySerialise')->willReturn(false);

        $this->factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with(\stdClass::class)
            ->willReturn($resource);
        
        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $result = $property->normaliseJsonValue($object);

        $this->assertNull($result);
    }

    public function testNormaliseJsonValueWithNonJsonSerializableObjectWhenCanAutoSerialise(): void
    {
        $object = new class {
            public string $name = 'test';
            public int $age = 25;
        };

        $property1 = $this->createPropertyMock();
        $property1->method('getPropertyName')->willReturn('name');
        $property1->method('normaliseJsonValue')->with('test')->willReturn('test');

        $property2 = $this->createPropertyMock();
        $property2->method('getPropertyName')->willReturn('age');
        $property2->method('normaliseJsonValue')->with(25)->willReturn(25);

        // Create a concrete collection that implements the Iterator interface properly
        $collection = new class([$property1, $property2]) implements CollectionInterface {
            private array $properties;
            private int $position = 0;

            public function __construct(array $properties)
            {
                $this->properties = $properties;
            }

            public function rewind(): void
            {
                $this->position = 0;
            }

            public function current(): ?PropertyInterface
            {
                return $this->properties[$this->position] ?? null;
            }

            public function key(): mixed
            {
                return $this->position;
            }

            public function next(): void
            {
                ++$this->position;
            }

            public function valid(): bool
            {
                return isset($this->properties[$this->position]);
            }

            public function offsetExists(mixed $offset): bool
            {
                return isset($this->properties[$offset]);
            }

            public function offsetGet(mixed $offset): ?PropertyInterface
            {
                return $this->properties[$offset] ?? null;
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
                if (\is_null($offset)) {
                    $this->properties[] = $value;
                } else {
                    $this->properties[$offset] = $value;
                }
            }

            public function offsetUnset(mixed $offset): void
            {
                unset($this->properties[$offset]);
            }
        };

        $resource = $this->createResourceMock();
        $resource->method('canAutomaticallySerialise')->willReturn(true);
        $resource->method('getProperties')->willReturn($collection);

        $this->factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with(\get_class($object))
            ->willReturn($resource);

        $property = new ClassProperty('test', \get_class($object), false, $this->factory);

        $result = $property->normaliseJsonValue($object);

        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertEquals((object)['name' => 'test', 'age' => 25], $result);
    }

    public function testCheckValueAgainstConstraintsWithNullAndNullable(): void
    {
        $path = $this->createPathMock();
        $property = new ClassProperty('test', \stdClass::class, true, $this->factory);

        $result = $property->checkValueAgainstConstraints($path, null);

        $this->assertTrue($result->isValid());
    }

    public function testCheckValueAgainstConstraintsWithNonIterableValue(): void
    {
        $path = $this->createPathMock();
        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $result = $property->checkValueAgainstConstraints($path, 'string');

        $this->assertFalse($result->isValid());
    }

    public function testCheckValueAgainstConstraintsWithArrayValue(): void
    {
        $path = $this->createPathMock();
        $resultMock = $this->createValidationResultMock();

        $resource = $this->createResourceMock();
        $resource
            ->expects($this->once())
            ->method('validateAndTranspose')
            ->with($path, $this->isType('array'))
            ->willReturn($resultMock);

        $this->factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with(\stdClass::class)
            ->willReturn($resource);

        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $result = $property->checkValueAgainstConstraints($path, []);

        $this->assertSame($resultMock, $result);
    }

    public function testCheckValueAgainstConstraintsWithObjectValue(): void
    {
        $path = $this->createPathMock();
        $resultMock = $this->createValidationResultMock();

        $resource = $this->createResourceMock();
        $resource
            ->expects($this->once())
            ->method('validateAndTranspose')
            ->with($path, $this->isType('object'))
            ->willReturn($resultMock);

        $this->factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with(\stdClass::class)
            ->willReturn($resource);

        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $result = $property->checkValueAgainstConstraints($path, new \stdClass());

        $this->assertSame($resultMock, $result);
    }

    public function testValidateAndTranspose(): void
    {
        $path = $this->createPathMock();
        $json = new \stdClass();
        $resultMock = $this->createValidationResultMock();

        $resource = $this->createResourceMock();
        $resource
            ->expects($this->once())
            ->method('validateAndTranspose')
            ->with($path, $json)
            ->willReturn($resultMock);

        $this->factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with(\stdClass::class)
            ->willReturn($resource);

        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $result = $property->validateAndTranspose($path, $json);

        $this->assertSame($resultMock, $result);
    }

    public function testCanAutomaticallySerialise(): void
    {
        $resource = $this->createResourceMock();
        $resource
            ->expects($this->once())
            ->method('canAutomaticallySerialise')
            ->willReturn(true);

        $this->factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with(\stdClass::class)
            ->willReturn($resource);

        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $result = $property->canAutomaticallySerialise();

        $this->assertTrue($result);
    }

    public function testGetProperties(): void
    {
        $collection = $this->createCollectionMock();

        $resource = $this->createResourceMock();
        $resource
            ->expects($this->once())
            ->method('getProperties')
            ->willReturn($collection);

        $this->factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with(\stdClass::class)
            ->willReturn($resource);

        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        $result = $property->getProperties();

        $this->assertSame($collection, $result);
    }

    public function testInnerResourceIsLazilyCreated(): void
    {
        $resource = $this->createResourceMock();

        $this->factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with(\stdClass::class)
            ->willReturn($resource);

        $property = new ClassProperty('test', \stdClass::class, false, $this->factory);

        // First call creates the resource
        $property->canAutomaticallySerialise();

        // Second call should use cached resource (factory should only be called once)
        $property->canAutomaticallySerialise();
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
}
