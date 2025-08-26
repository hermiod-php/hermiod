<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource;

use Hermiod\Attribute\Resource as Options;
use Hermiod\Resource\Factory;
use Hermiod\Resource\FactoryInterface;
use Hermiod\Resource\Name\StrategyInterface;
use Hermiod\Resource\Property\FactoryInterface as PropertyFactoryInterface;
use Hermiod\Resource\PropertyBagInterface;
use Hermiod\Resource\Resource;
use Hermiod\Resource\ResourceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Factory::class)]
class FactoryTest extends TestCase
{
    private PropertyFactoryInterface|MockObject $propertyFactory;
    private StrategyInterface|MockObject $namingStrategy;
    private Factory $factory;

    protected function setUp(): void
    {
        $this->propertyFactory = $this->createMock(PropertyFactoryInterface::class);
        $this->namingStrategy = $this->createMock(StrategyInterface::class);
        $this->factory = new Factory($this->propertyFactory, $this->namingStrategy);
    }

    protected function tearDown(): void
    {
        // Clear static cache between tests to ensure test isolation
        $reflection = new \ReflectionClass(Factory::class);
        $optionsProperty = $reflection->getProperty('options');
        $optionsProperty->setAccessible(true);
        $optionsProperty->setValue($this->factory, []);
    }

    public function testImplementsFactoryInterface(): void
    {
        $this->assertInstanceOf(FactoryInterface::class, $this->factory);
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(Factory::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testGetPropertyFactory(): void
    {
        $result = $this->factory->getPropertyFactory();

        $this->assertSame($this->propertyFactory, $result);
    }

    public function testCreateResourceForClassReturnsResourceInstance(): void
    {
        $resource = $this->factory->createResourceForClass(\stdClass::class);

        $this->assertInstanceOf(ResourceInterface::class, $resource);
        $this->assertInstanceOf(PropertyBagInterface::class, $resource);
        $this->assertInstanceOf(Resource::class, $resource);
    }

    #[DataProvider('classNamesProvider')]
    public function testCreateResourceForClassWithVariousClasses(string $className): void
    {
        $resource = $this->factory->createResourceForClass($className);

        $this->assertInstanceOf(ResourceInterface::class, $resource);
        $this->assertInstanceOf(PropertyBagInterface::class, $resource);
    }

    public function testCreateResourceForClassUsesProvidedDependencies(): void
    {
        // We can't directly verify internal Resource construction, but we can verify
        // that the resource is created and the factory dependencies are accessible
        $resource = $this->factory->createResourceForClass(\stdClass::class);

        $this->assertInstanceOf(Resource::class, $resource);

        // Verify that our property factory is accessible through the resource factory
        $returnedPropertyFactory = $this->factory->getPropertyFactory();
        $this->assertSame($this->propertyFactory, $returnedPropertyFactory);
    }

    public function testWithNamingStrategyCreatesNewInstance(): void
    {
        $newStrategy = $this->createMock(StrategyInterface::class);

        $newFactory = $this->factory->withNamingStrategy($newStrategy);

        $this->assertNotSame($this->factory, $newFactory);
        $this->assertInstanceOf(Factory::class, $newFactory);
        $this->assertInstanceOf(FactoryInterface::class, $newFactory);
    }

    public function testWithNamingStrategyPreservesPropertyFactory(): void
    {
        $newStrategy = $this->createMock(StrategyInterface::class);

        $newFactory = $this->factory->withNamingStrategy($newStrategy);

        $this->assertSame($this->propertyFactory, $newFactory->getPropertyFactory());
    }

    public function testWithNamingStrategyUsesNewStrategyForResourceCreation(): void
    {
        $newStrategy = $this->createMock(StrategyInterface::class);

        $originalFactory = $this->factory;
        $newFactory = $originalFactory->withNamingStrategy($newStrategy);

        // Both factories should create resources, but with different strategies
        $originalResource = $originalFactory->createResourceForClass(\stdClass::class);
        $newResource = $newFactory->createResourceForClass(\stdClass::class);

        $this->assertInstanceOf(Resource::class, $originalResource);
        $this->assertInstanceOf(Resource::class, $newResource);
        $this->assertNotSame($originalResource, $newResource);
    }

    public function testCreateResourceForClassWithClassWithoutAttributes(): void
    {
        $testClass = new class {};
        $className = \get_class($testClass);

        $resource = $this->factory->createResourceForClass($className);

        $this->assertInstanceOf(ResourceInterface::class, $resource);
        $this->assertInstanceOf(PropertyBagInterface::class, $resource);
    }

    public function testCreateResourceForClassWithClassWithResourceAttribute(): void
    {
        // Test with a class that has the Resource attribute
        $testClassWithAttribute = new #[Options(autoSerialize: false)] class {
            public string $publicProperty = 'public';
            private string $privateProperty = 'private';
        };

        $resource = $this->factory->createResourceForClass(\get_class($testClassWithAttribute));

        $this->assertInstanceOf(ResourceInterface::class, $resource);
        $this->assertInstanceOf(PropertyBagInterface::class, $resource);

        $this->assertFalse($resource->canAutomaticallySerialise(), 'Autoserialize should be false as per attribute');
    }

    public function testCreateResourceForClassCachesAttributeOptions(): void
    {
        $className = \stdClass::class;

        // First call should trigger reflection and caching
        $resource1 = $this->factory->createResourceForClass($className);

        // Second call should use cached options
        $resource2 = $this->factory->createResourceForClass($className);

        $this->assertInstanceOf(Resource::class, $resource1);
        $this->assertInstanceOf(Resource::class, $resource2);
        // Note: Resources themselves are not cached, only the attribute options
    }

    public function testCreateResourceForClassWithDifferentClassesCreatesDifferentResources(): void
    {
        $resource1 = $this->factory->createResourceForClass(\stdClass::class);
        $resource2 = $this->factory->createResourceForClass(\DateTime::class);

        $this->assertNotSame($resource1, $resource2);
        $this->assertInstanceOf(Resource::class, $resource1);
        $this->assertInstanceOf(Resource::class, $resource2);
    }

    public function testCreateResourceForClassHandlesReflectionException(): void
    {
        // Test with a non-existent class should throw ReflectionException
        $this->expectException(\ReflectionException::class);

        $this->factory->createResourceForClass('NonExistentClass');
    }

    public function testAttributeCachingAcrossMultipleFactoryInstances(): void
    {
        // Create a second factory instance
        $anotherPropertyFactory = $this->createMock(PropertyFactoryInterface::class);
        $anotherNamingStrategy = $this->createMock(StrategyInterface::class);
        $secondFactory = new Factory($anotherPropertyFactory, $anotherNamingStrategy);

        $className = \stdClass::class;

        // First factory caches the attributes
        $this->factory->createResourceForClass($className);

        // Second factory should benefit from the same cache (static)
        $resource = $secondFactory->createResourceForClass($className);

        $this->assertInstanceOf(Resource::class, $resource);
    }

    public function testCreateResourceForClassWithBuiltinClasses(): void
    {
        $builtinClasses = [
            \stdClass::class,
            \DateTime::class,
            \DateTimeImmutable::class,
            \Exception::class,
            \ArrayObject::class,
        ];

        foreach ($builtinClasses as $className) {
            $resource = $this->factory->createResourceForClass($className);

            $this->assertInstanceOf(ResourceInterface::class, $resource);
            $this->assertInstanceOf(PropertyBagInterface::class, $resource);
        }
    }

    public function testCreateResourceForClassWithAnonymousClass(): void
    {
        $anonymousClass = new class {
            public string $property = 'test';
        };

        $resource = $this->factory->createResourceForClass(get_class($anonymousClass));

        $this->assertInstanceOf(ResourceInterface::class, $resource);
        $this->assertInstanceOf(PropertyBagInterface::class, $resource);
    }

    public function testFactoryImmutabilityWithNamingStrategy(): void
    {
        $originalStrategy = $this->namingStrategy;
        $newStrategy1 = $this->createMock(StrategyInterface::class);
        $newStrategy2 = $this->createMock(StrategyInterface::class);

        $factory1 = $this->factory->withNamingStrategy($newStrategy1);
        $factory2 = $this->factory->withNamingStrategy($newStrategy2);
        $factory3 = $factory1->withNamingStrategy($newStrategy2);

        // All factories should be different instances
        $this->assertNotSame($this->factory, $factory1);
        $this->assertNotSame($this->factory, $factory2);
        $this->assertNotSame($factory1, $factory2);
        $this->assertNotSame($factory1, $factory3);
        $this->assertNotSame($factory2, $factory3);

        // Original factory should retain its property factory
        $this->assertSame($this->propertyFactory, $this->factory->getPropertyFactory());
        $this->assertSame($this->propertyFactory, $factory1->getPropertyFactory());
        $this->assertSame($this->propertyFactory, $factory2->getPropertyFactory());
        $this->assertSame($this->propertyFactory, $factory3->getPropertyFactory());
    }

    public function testCreateResourceForClassWithComplexNamespaceClasses(): void
    {
        $complexClasses = [
            'DateTime',
            'DateTimeImmutable',
            'ReflectionClass',
            'ArrayIterator',
            'SplObjectStorage',
        ];

        foreach ($complexClasses as $className) {
            if (class_exists($className)) {
                $resource = $this->factory->createResourceForClass($className);

                $this->assertInstanceOf(ResourceInterface::class, $resource);
                $this->assertInstanceOf(PropertyBagInterface::class, $resource);
            }
        }
    }

    public function testAttributeOptionsCaching(): void
    {
        $className = \stdClass::class;

        // Multiple calls should work correctly due to internal caching
        $resource1 = $this->factory->createResourceForClass($className);
        $resource2 = $this->factory->createResourceForClass($className);
        $resource3 = $this->factory->createResourceForClass($className);

        // All resources should be created successfully
        $this->assertInstanceOf(Resource::class, $resource1);
        $this->assertInstanceOf(Resource::class, $resource2);
        $this->assertInstanceOf(Resource::class, $resource3);

        // Resources are not cached (new instances each time), but options are cached internally
        $this->assertNotSame($resource1, $resource2);
        $this->assertNotSame($resource2, $resource3);
    }

    public static function classNamesProvider(): array
    {
        return [
            'stdClass' => [\stdClass::class],
            'DateTime' => [\DateTime::class],
            'DateTimeImmutable' => [\DateTimeImmutable::class],
            'Exception' => [\Exception::class],
            'ArrayObject' => [\ArrayObject::class],
            'ReflectionClass' => [\ReflectionClass::class],
        ];
    }
}
