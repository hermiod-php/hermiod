<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit;

use Hermiod\Converter;
use Hermiod\ConverterInterface;
use Hermiod\Exception\ConversionException;
use Hermiod\Resource\FactoryInterface;
use Hermiod\Resource\Name\StrategyInterface;
use Hermiod\Resource\Property\FactoryInterface as PropertyFactoryInterface;
use Hermiod\Resource\Property\Resolver\ResolverInterface;
use Hermiod\Resource\Unserializer\FactoryInterface as UnserializerFactoryInterface;
use Hermiod\Resource\Unserializer\UnserializerInterface;
use Hermiod\Tests\Unit\Fakes\FakeEmptyClass;
use Hermiod\Tests\Unit\Fakes\FakeUserClass;
use Hermiod\Result\Error\CollectionInterface;
use Hermiod\Result\ResultInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Converter::class)]
final class ConverterTest extends TestCase
{
    private FactoryInterface $resourceFactory;
    private UnserializerFactoryInterface $unserializerFactory;
    private PropertyFactoryInterface $propertyFactory;
    private ResolverInterface $resolver;
    private UnserializerInterface $unserializer;
    private ResultInterface $result;

    protected function setUp(): void
    {
        $this->resourceFactory = $this->createMock(FactoryInterface::class);
        $this->unserializerFactory = $this->createMock(UnserializerFactoryInterface::class);
        $this->propertyFactory = $this->createMock(PropertyFactoryInterface::class);
        $this->resolver = $this->createMock(ResolverInterface::class);
        $this->unserializer = $this->createMock(UnserializerInterface::class);
        $this->result = $this->createMock(ResultInterface::class);

        $this->resourceFactory
            ->method('getPropertyFactory')
            ->willReturn($this->propertyFactory);

        $this->propertyFactory
            ->method('getInterfaceResolver')
            ->willReturn($this->resolver);

        $this->unserializerFactory
            ->method('createUnserializerForClass')
            ->willReturn($this->unserializer);

        $this->unserializerFactory
            ->method('withResourceFactory')
            ->willReturnSelf();
    }

    public function testCreate(): void
    {
        $converter = Converter::create();

        $this->assertInstanceOf(Converter::class, $converter);
        $this->assertInstanceOf(ConverterInterface::class, $converter);
    }

    #[DataProvider('successfulConversionProvider')]
    public function testToClassWithSuccessfulConversion(string $class, mixed $json, object $expectedObject): void
    {
        $this->result
            ->method('getInstance')
            ->willReturn($expectedObject);

        $this->unserializer
            ->method('unserialize')
            ->with($json)
            ->willReturn($this->result);

        $converter = new Converter($this->resourceFactory, $this->unserializerFactory);
        $result = $converter->toClass($class, $json);

        $this->assertSame($expectedObject, $result);
    }

    #[DataProvider('failedConversionProvider')]
    public function testToClassWithFailedConversion(string $class, mixed $json): void
    {
        $errors = $this->createMock(CollectionInterface::class);

        $this->result
            ->method('getInstance')
            ->willReturn(null);

        $this->result
            ->method('getErrors')
            ->willReturn($errors);

        $this->unserializer
            ->method('unserialize')
            ->with($json)
            ->willReturn($this->result);

        $converter = new Converter($this->resourceFactory, $this->unserializerFactory);

        $this->expectException(ConversionException::class);
        $converter->toClass($class, $json);
    }

    #[DataProvider('tryToClassProvider')]
    public function testTryToClass(string $class, mixed $json): void
    {
        $this->unserializer
            ->method('unserialize')
            ->with($json)
            ->willReturn($this->result);

        $converter = new Converter($this->resourceFactory, $this->unserializerFactory);
        $result = $converter->tryToClass($class, $json);

        $this->assertSame($this->result, $result);
    }

    public function testTryToClassWithException(): void
    {
        $exception = new \Hermiod\Exception\JsonValueMustBeObjectException('test error', 404);

        $this->unserializer
            ->method('unserialize')
            ->willThrowException($exception);

        $converter = new Converter($this->resourceFactory, $this->unserializerFactory);

        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('test error');

        $converter->tryToClass(FakeUserClass::class, 'invalid json');
    }

    public function testToJsonReturnsNull(): void
    {
        $converter = new Converter($this->resourceFactory, $this->unserializerFactory);
        $object = new \stdClass();
        $result = $converter->toJson($object);

        $this->assertNull($result);
    }

    #[DataProvider('interfaceResolverProvider')]
    public function testAddInterfaceResolver(string $interface, string|callable $resolver): void
    {
        $this->resolver
            ->expects($this->once())
            ->method('addResolver')
            ->with($interface, $resolver);

        $converter = new Converter($this->resourceFactory, $this->unserializerFactory);
        $result = $converter->addInterfaceResolver($interface, $resolver);

        $this->assertSame($converter, $result);
        $this->assertInstanceOf(ConverterInterface::class, $result);
    }

    public function testUseNamingStrategy(): void
    {
        $strategy = $this->createMock(StrategyInterface::class);

        $this->resourceFactory
            ->expects($this->once())
            ->method('withNamingStrategy')
            ->willReturnSelf();

        $converter = new Converter($this->resourceFactory, $this->unserializerFactory);
        $result = $converter->useNamingStrategy($strategy);

        $this->assertSame($converter, $result);
        $this->assertInstanceOf(ConverterInterface::class, $result);
    }

    public function testUnserializerCaching(): void
    {
        $this->unserializerFactory
            ->expects($this->exactly(2))
            ->method('createUnserializerForClass')
            ->with(FakeUserClass::class)
            ->willReturn($this->unserializer);

        $this->unserializer
            ->expects($this->exactly(2))
            ->method('unserialize')
            ->willReturn($this->result);

        $converter = new Converter($this->resourceFactory, $this->unserializerFactory);

        // Call the same class twice to test caching behavior
        $converter->tryToClass(FakeUserClass::class, ['name' => 'John']);
        $converter->tryToClass(FakeUserClass::class, ['name' => 'Jane']);
    }

    public function testUseNamingStrategyClearsUnserializerCache(): void
    {
        $strategy = $this->createMock(StrategyInterface::class);

        $this->resourceFactory
            ->method('withNamingStrategy')
            ->willReturnSelf();

        $this->unserializerFactory
            ->expects($this->exactly(2)) // Should be called twice after cache clear
            ->method('createUnserializerForClass')
            ->with(FakeUserClass::class)
            ->willReturn($this->unserializer);

        $this->unserializer
            ->expects($this->exactly(2))
            ->method('unserialize')
            ->willReturn($this->result);

        $converter = new Converter($this->resourceFactory, $this->unserializerFactory);

        // First call - should create unserializer
        $converter->tryToClass(FakeUserClass::class, ['name' => 'test']);

        // Use naming strategy - should clear cache
        $converter->useNamingStrategy($strategy);

        // Second call - should create unserializer again
        $converter->tryToClass(FakeUserClass::class, ['name' => 'test']);
    }

    public static function successfulConversionProvider(): array
    {
        $userObject = new FakeUserClass();
        $userObject->name = 'John';
        $userObject->age = 25;
        $userObject->email = 'john@example.com';

        $emptyObject = new FakeEmptyClass();

        return [
            'string json with valid properties' => [
                FakeUserClass::class,
                '{"name": "John", "age": 25, "email": "john@example.com"}',
                $userObject
            ],
            'array json with valid properties' => [
                FakeUserClass::class,
                ['name' => 'Jane', 'age' => 30, 'email' => 'jane@example.com'],
                $userObject
            ],
            'empty object to empty class' => [
                FakeEmptyClass::class,
                [],
                $emptyObject
            ],
        ];
    }

    public static function failedConversionProvider(): array
    {
        return [
            'invalid string json' => [
                FakeUserClass::class,
                'invalid json'
            ],
            'properties not in class' => [
                FakeEmptyClass::class,
                ['nonExistentProperty' => 'value']
            ],
        ];
    }

    public static function tryToClassProvider(): array
    {
        return [
            'valid json string' => [
                FakeUserClass::class,
                '{"name": "test"}'
            ],
            'valid array' => [
                FakeUserClass::class,
                ['name' => 'test']
            ],
            'valid object' => [
                FakeUserClass::class,
                (object)['name' => 'test']
            ],
            'empty data to empty class' => [
                FakeEmptyClass::class,
                []
            ]
        ];
    }

    public static function interfaceResolverProvider(): array
    {
        return [
            'string resolver' => [
                'UserInterface',
                'ConcreteUser'
            ],
            'callable resolver' => [
                'PaymentInterface',
                fn(array $data): string => $data['type'] === 'credit' ? 'CreditPayment' : 'DebitPayment'
            ],
            'namespaced interface' => [
                'App\\Repository\\UserRepositoryInterface',
                'App\\Repository\\EloquentUserRepository'
            ],
        ];
    }
}

