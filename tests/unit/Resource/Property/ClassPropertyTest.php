<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property;

use Hermiod\Resource\FactoryInterface;
use Hermiod\Resource\Property\CollectionInterface;
use Hermiod\Resource\Property\Exception\PropertyClassTypeNotFoundException;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\ClassProperty;
use Hermiod\Resource\Property\Traits\ConstructWithNameAndNullableTrait;
use Hermiod\Resource\Property\Traits\ConvertToSameJsonValue;
use Hermiod\Resource\ResourceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClassProperty::class)]
#[CoversClass(ConstructWithNameAndNullableTrait::class)]
#[CoversClass(ConvertToSameJsonValue::class)]
class ClassPropertyTest extends TestCase
{
    public function testImplementsPropertyInterface(): void
    {
        $this->assertInstanceOf(
            PropertyInterface::class,
            new ClassProperty(
                'foo',
                \get_class($this->createSerializableFake()),
                true,
                $this->createFactoryMock(),
            ),
        );
    }

    public function testGetPropertyName(): void
    {
        $property = new ClassProperty(
            'foo',
            \get_class($this->createSerializableFake()),
            true,
            $this->createFactoryMock(),
        );

        $this->assertSame('foo', $property->getPropertyName());
    }

    public function testGetWithoutDefaultNull(): void
    {
        $property = new ClassProperty(
            'foo',
            \get_class($this->createSerializableFake()),
            true,
            $this->createFactoryMock(),
        );

        $this->assertFalse($property->hasDefaultValue());
        $this->assertNull($property->getDefaultValue());
    }

    public function testGetWithDefaultNull(): void
    {
        $property = ClassProperty::withDefaultNullValue(
            'foo',
            \get_class($this->createSerializableFake()),
            true,
            $this->createFactoryMock(),
        );

        $this->assertTrue($property->hasDefaultValue());
        $this->assertNull($property->getDefaultValue());
    }

    public function testThrowsOnInvalidClassname(): void
    {
        $this->expectException(PropertyClassTypeNotFoundException::class);

        new ClassProperty(
            'foo',
            '\\A\\Class\\Which\\Does\\Not\\Exist',
            true,
            $this->createFactoryMock(),
        );
    }

    public function testCanJsonEncodeValueOfCorrectClassWhenJsonSerialisable(): void
    {
        $invocations = 0;
        $expected = ['foo' => 'bar'];

        $fake = $this->createSerializableFake(function () use (&$invocations, $expected) {
            $invocations++;
            return $expected;
        });

        $factory = $this->createFactoryMock();

        $property = new ClassProperty(
            'foo',
            \get_class($fake),
            true,
            $factory,
        );

        $this->assertSame(
            $expected,
            $property->normaliseJsonValue($fake),
        );

        $this->assertSame(
            1,
            $invocations,
            \sprintf(
                'Expected %s::jsonSerialize() to be called %s times but was called %s times',
                \get_class($fake),
                1,
                $invocations,
            )
        );
    }

    private function createSerializableFake(?\Closure $method = null): \JsonSerializable
    {
        $method ??= fn () => null;

        $fake = new class ($method) implements \JsonSerializable
        {
            public function __construct(
                private ?\Closure $method,
            ) {}

            public function jsonSerialize(): mixed
            {
                return $this->method->__invoke();
            }
        };

        return $fake;
    }

    private function createFactoryMock(array $properties = []): FactoryInterface & MockObject
    {
        $factory = $this->createMock(FactoryInterface::class);

        $factory
            ->method('createResourceForClass')
            ->willReturnCallback(function (string $class): ResourceInterface {
                $resource = $this->createMock(ResourceInterface::class);
                $collection = $this->createMock(CollectionInterface::class);

                $resource
                    ->method('getProperties')
                    ->willReturn($collection);

                $collection
                    ->method('offsetExists')
                    ->willReturnCallback(function (string $key) use (&$properties) {
                        return isset($properties[$key]);
                    });

                $collection
                    ->method('offsetGet')
                    ->willReturnCallback(function (string $key) use (&$properties) {
                        return $properties[$key] ?? null;
                    });

                return $resource;
            });

        return $factory;
    }
}
