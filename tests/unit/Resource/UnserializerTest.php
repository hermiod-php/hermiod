<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource;

use Hermiod\Exception\JsonValueMustBeObjectException;
use Hermiod\Resource\FactoryInterface;
use Hermiod\Resource\Hydrator\FactoryInterface as HydratorFactoryInterface;
use Hermiod\Resource\Hydrator\HydratorInterface;
use Hermiod\Resource\ResourceInterface;
use Hermiod\Resource\Unserializer;
use Hermiod\Resource\UnserializerInterface;
use Hermiod\Result\ResultInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Unserializer::class)]
class UnserializerTest extends TestCase
{
    public function testImplementsUnserializerInterface(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $class = 'TestClass';

        $unserializer = new Unserializer($factory, $hydrator, $class);

        $this->assertInstanceOf(UnserializerInterface::class, $unserializer);
    }

    public function testUnserializeWithValidJsonString(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';
        $json = '{"name": "test", "value": 123}';

        $this->setupMockExpectations($factory, $hydrator, $resource, $hydratorMock, $class);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize($json);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testUnserializeWithValidJsonObject(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';
        $object = (object) ['name' => 'test', 'value' => 123];

        $this->setupMockExpectations($factory, $hydrator, $resource, $hydratorMock, $class);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize($object);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testUnserializeWithValidAssociativeArray(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';
        $array = ['name' => 'test', 'value' => 123];

        $this->setupMockExpectations($factory, $hydrator, $resource, $hydratorMock, $class);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize($array);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    #[DataProvider('validJsonStringProvider')]
    public function testUnserializeWithVariousValidJsonStrings(string $json): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';

        $this->setupMockExpectations($factory, $hydrator, $resource, $hydratorMock, $class);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize($json);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    #[DataProvider('validObjectProvider')]
    public function testUnserializeWithVariousValidObjects(object $object): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';

        $this->setupMockExpectations($factory, $hydrator, $resource, $hydratorMock, $class);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize($object);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    #[DataProvider('validAssociativeArrayProvider')]
    public function testUnserializeWithVariousValidAssociativeArrays(array $array): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';

        $this->setupMockExpectations($factory, $hydrator, $resource, $hydratorMock, $class);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize($array);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    #[DataProvider('invalidJsonValueProvider')]
    public function testUnserializeThrowsExceptionForInvalidValues(mixed $invalid): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $class = 'TestClass';

        $unserializer = new Unserializer($factory, $hydrator, $class);

        $this->expectException(JsonValueMustBeObjectException::class);

        $unserializer->unserialize($invalid);
    }

    public function testUnserializeThrowsExceptionForInvalidJsonString(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $class = 'TestClass';
        $invalid = '{"invalid": json}';

        $unserializer = new Unserializer($factory, $hydrator, $class);

        $this->expectException(\JsonException::class);

        $unserializer->unserialize($invalid);
    }

    public function testUnserializeThrowsExceptionForListArray(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $class = 'TestClass';
        $list = [1, 2, 3, 'test'];

        $unserializer = new Unserializer($factory, $hydrator, $class);

        $this->expectException(JsonValueMustBeObjectException::class);

        $unserializer->unserialize($list);
    }

    public static function validJsonStringProvider(): array
    {
        return [
            'simple object' => ['{"name": "test"}'],
            'nested object' => ['{"user": {"name": "test", "age": 30}}'],
            'object with array' => ['{"items": [1, 2, 3], "name": "test"}'],
            'object with null' => ['{"value": null, "name": "test"}'],
            'object with boolean' => ['{"active": true, "name": "test"}'],
            'object with float' => ['{"price": 19.99, "name": "test"}'],
            'empty object' => ['{}'],
            'object with special characters' => ['{"name": "test with spaces & symbols!"}'],
            'object with unicode' => ['{"name": "测试", "emoji": "🎉"}'],
        ];
    }

    public static function validObjectProvider(): array
    {
        return [
            'simple stdClass' => [(object) ['name' => 'test']],
            'nested stdClass' => [(object) ['user' => (object) ['name' => 'test']]],
            'stdClass with array' => [(object) ['items' => [1, 2, 3]]],
            'stdClass with null' => [(object) ['value' => null]],
            'stdClass with boolean' => [(object) ['active' => true]],
            'empty stdClass' => [new \stdClass()],
        ];
    }

    public static function validAssociativeArrayProvider(): array
    {
        return [
            'simple array' => [['name' => 'test']],
            'nested array' => [['user' => ['name' => 'test']]],
            'array with list' => [['items' => [1, 2, 3]]],
            'array with null' => [['value' => null]],
            'array with boolean' => [['active' => true]],
            'array with float' => [['price' => 19.99]],
            'empty array' => [[]],
            'array with mixed types' => [['string' => 'test', 'int' => 42, 'bool' => true, 'null' => null]],
        ];
    }

    public static function invalidJsonValueProvider(): array
    {
        return [
            'string that decodes to list' => ['[1, 2, 3]'],
            'string that decodes to primitive' => ['"test"'],
            'string that decodes to number' => ['123'],
            'string that decodes to boolean' => ['true'],
            'string that decodes to null' => ['null'],
            'list array' => [[1, 2, 3]],
            'mixed list array' => [['test', 123, true]],
        ];
    }

    public function testResourceFactoryIsCalledWithCorrectClass(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';

        $factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with($class)
            ->willReturn($resource);

        $hydrator
            ->expects($this->once())
            ->method('createHydrator')
            ->willReturn($hydratorMock);

        $unserializer = new Unserializer($factory, $hydrator, $class);

        $unserializer->unserialize(['test' => 'value']);
    }

    public function testHydratorFactoryIsCalledOnce(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';

        $factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->willReturn($resource);

        $hydrator
            ->expects($this->once())
            ->method('createHydrator')
            ->willReturn($hydratorMock);

        $unserializer = new Unserializer($factory, $hydrator, $class);

        $unserializer->unserialize(['test' => 'value']);
    }

    public function testJsonStringDecodingWithJsonThrowOnError(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $class = 'TestClass';
        $invalid = '{"invalid": json syntax}';

        $unserializer = new Unserializer($factory, $hydrator, $class);

        $this->expectException(\JsonException::class);

        $unserializer->unserialize($invalid);
    }

    #[DataProvider('malformedJsonProvider')]
    public function testUnserializeThrowsJsonExceptionForMalformedJson(string $malformed): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $class = 'TestClass';

        $unserializer = new Unserializer($factory, $hydrator, $class);

        $this->expectException(\JsonException::class);

        $unserializer->unserialize($malformed);
    }

    public static function malformedJsonProvider(): array
    {
        return [
            'missing closing brace' => ['{"name": "test"'],
            'missing quotes on key' => ['{name: "test"}'],
            'trailing comma' => ['{"name": "test",}'],
            'single quotes' => ["{'name': 'test'}"],
            'missing colon' => ['{"name" "test"}'],
            'unescaped quotes' => ['{"name": "test "quote""}'],
            'invalid escape sequence' => ['{"name": "test\\x"}'],
        ];
    }

    public function testArrayIsListDetection(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $class = 'TestClass';
        $associative = ['key' => 'value', 'another' => 'test'];
        $list = [0 => 'first', 1 => 'second', 2 => 'third'];

        // First test - valid associative array
        $factory1 = $this->createResourceFactory();
        $hydrator1 = $this->createHydratorFactory();
        $resource1 = $this->createResource();
        $hydratorMock1 = $this->createHydrator();

        $this->setupMockExpectations($factory1, $hydrator1, $resource1, $hydratorMock1, $class);

        $unserializer1 = new Unserializer($factory1, $hydrator1, $class);
        $result = $unserializer1->unserialize($associative);
        $this->assertInstanceOf(ResultInterface::class, $result);

        // Second test - invalid list array
        $unserializer2 = new Unserializer($factory, $hydrator, $class);

        $this->expectException(JsonValueMustBeObjectException::class);
        $unserializer2->unserialize($list);
    }

    public function testEmptyArrayIsNotList(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';

        $this->setupMockExpectations($factory, $hydrator, $resource, $hydratorMock, $class);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize([]);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testGenericTypeHandling(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'Specific\\Class\\Name';

        $factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with($class)
            ->willReturn($resource);

        $hydrator
            ->expects($this->once())
            ->method('createHydrator')
            ->willReturn($hydratorMock);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize(['test' => 'value']);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    #[DataProvider('classNameProvider')]
    public function testUnserializeWithVariousClassNames(string $class): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();

        $factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with($class)
            ->willReturn($resource);

        $hydrator
            ->expects($this->once())
            ->method('createHydrator')
            ->willReturn($hydratorMock);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize(['test' => 'value']);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testFactoryExceptionsArePropagated(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $class = 'TestClass';
        $exception = new \RuntimeException('Factory failed');

        $factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->willThrowException($exception);

        $unserializer = new Unserializer($factory, $hydrator, $class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Factory failed');

        $unserializer->unserialize(['test' => 'value']);
    }

    public function testHydratorFactoryExceptionsArePropagated(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $class = 'TestClass';
        $exception = new \RuntimeException('Hydrator factory failed');

        $factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->willReturn($resource);

        $hydrator
            ->expects($this->once())
            ->method('createHydrator')
            ->willThrowException($exception);

        $unserializer = new Unserializer($factory, $hydrator, $class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Hydrator factory failed');

        $unserializer->unserialize(['test' => 'value']);
    }

    public function testJsonDecodingWithAssociativeFlagIsTrue(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';
        $json = '{"nested": {"key": "value"}}';

        $this->setupMockExpectations($factory, $hydrator, $resource, $hydratorMock, $class);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize($json);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testJsonThrowOnErrorFlagIsUsed(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $class = 'TestClass';
        $invalid = '{"invalid";}';

        $unserializer = new Unserializer($factory, $hydrator, $class);

        $this->expectException(\JsonException::class);

        $unserializer->unserialize($invalid);
    }

    #[DataProvider('edgeCaseJsonProvider')]
    public function testUnserializeWithEdgeCaseJson(string $json): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';

        $this->setupMockExpectations($factory, $hydrator, $resource, $hydratorMock, $class);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize($json);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testResultObjectIsReturned(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';

        $this->setupMockExpectations($factory, $hydrator, $resource, $hydratorMock, $class);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize(['test' => 'value']);

        $this->assertInstanceOf(\Hermiod\Result\Result::class, $result);
    }

    public function testHydratorIsPassedToResult(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';

        $factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with($class)
            ->willReturn($resource);

        $hydrator
            ->expects($this->once())
            ->method('createHydrator')
            ->willReturn($hydratorMock);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize(['test' => 'value']);

        $this->assertInstanceOf(\Hermiod\Result\Result::class, $result);
    }

    public function testResourceIsPassedToResult(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';

        $factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with($class)
            ->willReturn($resource);

        $hydrator
            ->expects($this->once())
            ->method('createHydrator')
            ->willReturn($hydratorMock);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize(['test' => 'value']);

        $this->assertInstanceOf(\Hermiod\Result\Result::class, $result);
    }

    public function testJsonIsPassedToResult(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';
        $data = ['name' => 'test', 'value' => 123];

        $factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with($class)
            ->willReturn($resource);

        $hydrator
            ->expects($this->once())
            ->method('createHydrator')
            ->willReturn($hydratorMock);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize($data);

        $this->assertInstanceOf(\Hermiod\Result\Result::class, $result);
    }

    public function testObjectInputIsPassedDirectlyToResult(): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $resource = $this->createResource();
        $hydratorMock = $this->createHydrator();
        $class = 'TestClass';
        $object = (object) ['name' => 'test', 'value' => 123];

        $factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with($class)
            ->willReturn($resource);

        $hydrator
            ->expects($this->once())
            ->method('createHydrator')
            ->willReturn($hydratorMock);

        $unserializer = new Unserializer($factory, $hydrator, $class);
        $result = $unserializer->unserialize($object);

        $this->assertInstanceOf(\Hermiod\Result\Result::class, $result);
    }

    #[DataProvider('primitivesFromJsonStringProvider')]
    public function testUnserializeThrowsExceptionForJsonStringThatDecodesToPrimitive(string $primitive): void
    {
        $factory = $this->createResourceFactory();
        $hydrator = $this->createHydratorFactory();
        $class = 'TestClass';

        $unserializer = new Unserializer($factory, $hydrator, $class);

        $this->expectException(JsonValueMustBeObjectException::class);

        $unserializer->unserialize($primitive);
    }

    public static function classNameProvider(): array
    {
        return [
            'simple class' => ['TestClass'],
            'namespaced class' => ['Namespace\\TestClass'],
            'deeply namespaced class' => ['Deeply\\Nested\\Namespace\\TestClass'],
            'class with numbers' => ['Test123Class'],
            'class with underscores' => ['Test_Class_Name'],
        ];
    }

    public static function edgeCaseJsonProvider(): array
    {
        return [
            'deeply nested object' => ['{"a": {"b": {"c": {"d": "test"}}}}'],
            'object with large number' => ['{"value": 123456789012345}'],
            'object with scientific notation' => ['{"value": 1.23e+10}'],
            'object with escaped characters' => ['{"path": "C:\\\\Users\\\\test"}'],
            'object with unicode' => ['{"emoji": "🚀", "chinese": "你好"}'],
            'object with empty string' => ['{"empty": ""}'],
            'object with zero' => ['{"zero": 0}'],
            'object with negative number' => ['{"negative": -42}'],
            'object with very long string' => ['{"long": "' . \str_repeat('a', 1000) . '"}'],
        ];
    }

    public static function primitivesFromJsonStringProvider(): array
    {
        return [
            'string primitive' => ['"test"'],
            'number primitive' => ['123'],
            'boolean true' => ['true'],
            'boolean false' => ['false'],
            'null primitive' => ['null'],
            'float primitive' => ['12.34'],
            'negative number' => ['-42'],
            'scientific notation' => ['1.23e+10'],
        ];
    }

    private function setupMockExpectations(
        FactoryInterface|MockObject $factory,
        HydratorFactoryInterface|MockObject $hydrator,
        ResourceInterface|MockObject $resource,
        HydratorInterface|MockObject $hydratorMock,
        string $class
    ): void {
        $factory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with($class)
            ->willReturn($resource);

        $hydrator
            ->expects($this->once())
            ->method('createHydrator')
            ->willReturn($hydratorMock);
    }

    private function createResourceFactory(): FactoryInterface|MockObject
    {
        return $this->createMock(FactoryInterface::class);
    }

    private function createHydratorFactory(): HydratorFactoryInterface|MockObject
    {
        return $this->createMock(HydratorFactoryInterface::class);
    }

    private function createHydrator(): HydratorInterface|MockObject
    {
        return $this->createMock(HydratorInterface::class);
    }

    private function createResource(): ResourceInterface|MockObject
    {
        return $this->createMock(ResourceInterface::class);
    }

    private function createResult(): ResultInterface|MockObject
    {
        return $this->createMock(ResultInterface::class);
    }
}
