<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource;

use Hermiod\Resource\FactoryInterface;
use Hermiod\Resource\Name\StrategyInterface;
use Hermiod\Resource\Property\FactoryInterface as PropertyFactoryInterface;
use Hermiod\Resource\ProxyCallbackFactory;
use Hermiod\Resource\ResourceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProxyCallbackFactory::class)]
#[Medium]
class ProxyCallbackFactoryTest extends TestCase
{
    private FactoryInterface|MockObject $wrappedFactory;
    private PropertyFactoryInterface|MockObject $propertyFactory;
    private StrategyInterface|MockObject $namingStrategy;

    protected function setUp(): void
    {
        $this->wrappedFactory = $this->createMock(FactoryInterface::class);
        $this->propertyFactory = $this->createMock(PropertyFactoryInterface::class);
        $this->namingStrategy = $this->createMock(StrategyInterface::class);
    }

    public function testImplementsFactoryInterface(): void
    {
        $resolver = fn () => $this->wrappedFactory;
        $factory = new ProxyCallbackFactory($resolver);

        $this->assertInstanceOf(FactoryInterface::class, $factory);
    }

    public function testCreateResourceForClassDelegatesToWrappedFactory(): void
    {
        $className = 'TestClass';
        $expectedResource = $this->createMock(ResourceInterface::class);

        $this->wrappedFactory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with($className)
            ->willReturn($expectedResource);

        $resolver = fn () => $this->wrappedFactory;
        $factory = new ProxyCallbackFactory($resolver);

        $result = $factory->createResourceForClass($className);

        $this->assertSame($expectedResource, $result);
    }

    #[DataProvider('classNameProvider')]
    public function testCreateResourceForClassWithVariousClassNames(string $className): void
    {
        $expectedResource = $this->createMock(ResourceInterface::class);

        $this->wrappedFactory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with($className)
            ->willReturn($expectedResource);

        $resolver = fn () => $this->wrappedFactory;
        $factory = new ProxyCallbackFactory($resolver);

        $result = $factory->createResourceForClass($className);

        $this->assertSame($expectedResource, $result);
    }

    public static function classNameProvider(): array
    {
        return [
            'simple class name' => ['SimpleClass'],
            'namespaced class' => ['Some\\Namespace\\Class'],
            'fully qualified class' => ['\\Fully\\Qualified\\Class'],
            'class with numbers' => ['Class123'],
            'class with underscores' => ['Class_With_Underscores'],
            'nested namespace' => ['Very\\Deep\\Nested\\Namespace\\Class'],
        ];
    }

    public function testGetPropertyFactoryDelegatesToWrappedFactory(): void
    {
        $this->wrappedFactory
            ->expects($this->once())
            ->method('getPropertyFactory')
            ->willReturn($this->propertyFactory);

        $resolver = fn () => $this->wrappedFactory;
        $factory = new ProxyCallbackFactory($resolver);

        $result = $factory->getPropertyFactory();

        $this->assertSame($this->propertyFactory, $result);
    }

    public function testWithNamingStrategyCreatesNewProxyInstance(): void
    {
        $resolver = fn () => $this->wrappedFactory;
        $factory = new ProxyCallbackFactory($resolver);

        $result = $factory->withNamingStrategy($this->namingStrategy);

        $this->assertInstanceOf(ProxyCallbackFactory::class, $result);
        $this->assertNotSame($factory, $result);
    }

    public function testWithNamingStrategyReturnedProxyDelegatesToNewFactory(): void
    {
        $newFactory = $this->createMock(FactoryInterface::class);
        $expectedResource = $this->createMock(ResourceInterface::class);

        $this->wrappedFactory
            ->expects($this->once())
            ->method('withNamingStrategy')
            ->with($this->namingStrategy)
            ->willReturn($newFactory);

        $newFactory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with('TestClass')
            ->willReturn($expectedResource);

        $resolver = fn () => $this->wrappedFactory;
        $factory = new ProxyCallbackFactory($resolver);

        $newProxyFactory = $factory->withNamingStrategy($this->namingStrategy);
        $result = $newProxyFactory->createResourceForClass('TestClass');

        $this->assertSame($expectedResource, $result);
    }

    public function testResolverIsCalledOnEachMethodCall(): void
    {
        $callCount = 0;
        $resolver = function () use (&$callCount) {
            $callCount++;
            return $this->wrappedFactory;
        };

        $mockResource = $this->createMock(ResourceInterface::class);

        $this->wrappedFactory
            ->expects($this->exactly(3))
            ->method('createResourceForClass')
            ->willReturn($mockResource);

        $this->wrappedFactory
            ->expects($this->once())
            ->method('getPropertyFactory')
            ->willReturn($this->propertyFactory);

        $factory = new ProxyCallbackFactory($resolver);

        $factory->createResourceForClass('Class1');
        $factory->createResourceForClass('Class2');
        $factory->createResourceForClass('Class3');
        $factory->getPropertyFactory();

        $this->assertEquals(4, $callCount);
    }

    public function testResolverThrowsTypeErrorWhenReturningIncorrectType(): void
    {
        $resolver = fn () => 'not a factory';
        $factory = new ProxyCallbackFactory($resolver);

        $this->expectException(\TypeError::class);

        $factory->createResourceForClass('TestClass');
    }

    #[DataProvider('invalidResolverReturnProvider')]
    public function testResolverThrowsTypeErrorWithVariousInvalidTypes(mixed $invalidReturn): void
    {
        $resolver = fn () => $invalidReturn;
        $factory = new ProxyCallbackFactory($resolver);

        $this->expectException(\TypeError::class);

        $factory->createResourceForClass('TestClass');
    }

    public static function invalidResolverReturnProvider(): array
    {
        return [
            'null' => [null],
            'string' => ['string'],
            'integer' => [42],
            'float' => [3.14],
            'boolean true' => [true],
            'boolean false' => [false],
            'array' => [[]],
            'stdClass object' => [new \stdClass()],
            'callable' => [fn () => null],
        ];
    }

    public function testResolverExceptionIsPropagated(): void
    {
        $expectedException = new \RuntimeException('Resolver failed');
        $resolver = function () use ($expectedException) {
            throw $expectedException;
        };

        $factory = new ProxyCallbackFactory($resolver);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Resolver failed');

        $factory->createResourceForClass('TestClass');
    }

    public function testResolverCanReturnDifferentFactoriesOnSubsequentCalls(): void
    {
        $factory1 = $this->createMock(FactoryInterface::class);
        $factory2 = $this->createMock(FactoryInterface::class);
        $resource1 = $this->createMock(ResourceInterface::class);
        $resource2 = $this->createMock(ResourceInterface::class);

        $callCount = 0;
        $resolver = function () use (&$callCount, $factory1, $factory2) {
            $callCount++;
            return $callCount === 1 ? $factory1 : $factory2;
        };

        $factory1
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with('Class1')
            ->willReturn($resource1);

        $factory2
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with('Class2')
            ->willReturn($resource2);

        $proxyFactory = new ProxyCallbackFactory($resolver);

        $result1 = $proxyFactory->createResourceForClass('Class1');
        $result2 = $proxyFactory->createResourceForClass('Class2');

        $this->assertSame($resource1, $result1);
        $this->assertSame($resource2, $result2);
    }

    public function testLazyResolutionDoesNotCallResolverOnConstruction(): void
    {
        $called = false;
        $resolver = function () use (&$called) {
            $called = true;
            return $this->wrappedFactory;
        };

        new ProxyCallbackFactory($resolver);

        $this->assertFalse($called);
    }

    public function testClosureCapturingVariables(): void
    {
        $mockFactory = $this->createMock(FactoryInterface::class);
        $mockResource = $this->createMock(ResourceInterface::class);

        $mockFactory
            ->expects($this->once())
            ->method('createResourceForClass')
            ->with('TestClass')
            ->willReturn($mockResource);

        $resolver = fn () => $mockFactory;

        $factory = new ProxyCallbackFactory($resolver);
        $resource = $factory->createResourceForClass('TestClass');

        // Verify the captured value was used
        $this->assertInstanceOf(ResourceInterface::class, $resource);
    }

    public function testWithNamingStrategyLazyResolution(): void
    {
        $resolverCallCount = 0;
        $resolver = function () use (&$resolverCallCount) {
            $resolverCallCount++;
            return $this->wrappedFactory;
        };

        $newFactory = $this->createMock(FactoryInterface::class);
        $this->wrappedFactory
            ->expects($this->once())
            ->method('withNamingStrategy')
            ->willReturn($newFactory);

        $factory = new ProxyCallbackFactory($resolver);

        // Creating a new proxy with withNamingStrategy doesn't call the resolver yet
        $newProxyFactory = $factory->withNamingStrategy($this->namingStrategy);

        // Resolver should not be called until we actually use the new factory
        $this->assertEquals(0, $resolverCallCount);

        // Now use the new factory - this will trigger the resolver
        $newFactory
            ->expects($this->once())
            ->method('getPropertyFactory')
            ->willReturn($this->propertyFactory);

        $newProxyFactory->getPropertyFactory();

        // The new factory's resolver should have been called once
        $this->assertEquals(1, $resolverCallCount);
    }

    public function testMultipleMethodCallsOnSameInstance(): void
    {
        $resource1 = $this->createMock(ResourceInterface::class);
        $resource2 = $this->createMock(ResourceInterface::class);

        $this->wrappedFactory
            ->expects($this->exactly(2))
            ->method('createResourceForClass')
            ->willReturnCallback(function (string $class) use ($resource1, $resource2) {
                return $class === 'Class1' ? $resource1 : $resource2;
            });

        $this->wrappedFactory
            ->expects($this->once())
            ->method('getPropertyFactory')
            ->willReturn($this->propertyFactory);

        $resolver = fn () => $this->wrappedFactory;
        $factory = new ProxyCallbackFactory($resolver);

        $result1 = $factory->createResourceForClass('Class1');
        $propertyFactory = $factory->getPropertyFactory();
        $result2 = $factory->createResourceForClass('Class2');

        $this->assertSame($resource1, $result1);
        $this->assertSame($this->propertyFactory, $propertyFactory);
        $this->assertSame($resource2, $result2);
    }
}
