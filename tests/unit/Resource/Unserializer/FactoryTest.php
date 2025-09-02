<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Unserializer;

use Hermiod\Resource\FactoryInterface as ResourceFactoryInterface;
use Hermiod\Resource\Hydrator\FactoryInterface as HydratorFactoryInterface;
use Hermiod\Resource\Unserializer\Factory;
use Hermiod\Resource\Unserializer\FactoryInterface;
use Hermiod\Resource\Unserializer\UnserializerInterface;
use Hermiod\Tests\Unit\Fakes\FakeEmptyClass;
use Hermiod\Tests\Unit\Fakes\FakeUserClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Factory::class)]
final class FactoryTest extends TestCase
{
    private ResourceFactoryInterface $resourceFactory;
    private HydratorFactoryInterface $hydratorFactory;

    protected function setUp(): void
    {
        $this->resourceFactory = $this->createMock(ResourceFactoryInterface::class);
        $this->hydratorFactory = $this->createMock(HydratorFactoryInterface::class);
    }

    public function testImplementsFactoryInterface(): void
    {
        $factory = new Factory($this->resourceFactory, $this->hydratorFactory);

        $this->assertInstanceOf(FactoryInterface::class, $factory);
    }

    public function testCreateUnserializerForClass(): void
    {
        $factory = new Factory($this->resourceFactory, $this->hydratorFactory);
        $unserializer = $factory->createUnserializerForClass(FakeUserClass::class);

        $this->assertInstanceOf(UnserializerInterface::class, $unserializer);
    }

    public function testCreateUnserializerForClassWithDifferentClasses(): void
    {
        $factory = new Factory($this->resourceFactory, $this->hydratorFactory);
        
        $userUnserializer = $factory->createUnserializerForClass(FakeUserClass::class);
        $emptyUnserializer = $factory->createUnserializerForClass(FakeEmptyClass::class);

        $this->assertInstanceOf(UnserializerInterface::class, $userUnserializer);
        $this->assertInstanceOf(UnserializerInterface::class, $emptyUnserializer);
        $this->assertNotSame($userUnserializer, $emptyUnserializer);
    }

    public function testCachingBehavior(): void
    {
        $factory = new Factory($this->resourceFactory, $this->hydratorFactory);
        
        $firstCall = $factory->createUnserializerForClass(FakeUserClass::class);
        $secondCall = $factory->createUnserializerForClass(FakeUserClass::class);

        $this->assertSame($firstCall, $secondCall, 'Should return the same instance when called with the same class');
    }

    public function testCachingWithMultipleClasses(): void
    {
        $factory = new Factory($this->resourceFactory, $this->hydratorFactory);
        
        $userUnserializer1 = $factory->createUnserializerForClass(FakeUserClass::class);
        $emptyUnserializer1 = $factory->createUnserializerForClass(FakeEmptyClass::class);
        $userUnserializer2 = $factory->createUnserializerForClass(FakeUserClass::class);
        $emptyUnserializer2 = $factory->createUnserializerForClass(FakeEmptyClass::class);

        $this->assertSame($userUnserializer1, $userUnserializer2);
        $this->assertSame($emptyUnserializer1, $emptyUnserializer2);
        $this->assertNotSame($userUnserializer1, $emptyUnserializer1);
    }

    public function testWithResourceFactory(): void
    {
        $originalFactory = new Factory($this->resourceFactory, $this->hydratorFactory);
        $newResourceFactory = $this->createMock(ResourceFactoryInterface::class);
        
        $newFactory = $originalFactory->withResourceFactory($newResourceFactory);

        $this->assertNotSame($originalFactory, $newFactory);
        $this->assertInstanceOf(Factory::class, $newFactory);
        $this->assertInstanceOf(FactoryInterface::class, $newFactory);
    }

    public function testWithResourceFactoryCreatesNewInstance(): void
    {
        $originalFactory = new Factory($this->resourceFactory, $this->hydratorFactory);
        $newResourceFactory = $this->createMock(ResourceFactoryInterface::class);
        
        $newFactory = $originalFactory->withResourceFactory($newResourceFactory);

        $this->assertNotSame($originalFactory, $newFactory);
    }

    public function testWithResourceFactoryClearsCache(): void
    {
        $originalFactory = new Factory($this->resourceFactory, $this->hydratorFactory);
        $newResourceFactory = $this->createMock(ResourceFactoryInterface::class);
        
        // Create an unserializer to populate the cache
        $originalUnserializer = $originalFactory->createUnserializerForClass(FakeUserClass::class);
        
        // Create new factory with different resource factory
        $newFactory = $originalFactory->withResourceFactory($newResourceFactory);
        
        // Create unserializer with the same class - should be a new instance due to cache clear
        $newUnserializer = $newFactory->createUnserializerForClass(FakeUserClass::class);
        
        $this->assertNotSame($originalUnserializer, $newUnserializer);
    }

    public function testWithResourceFactoryReturnsSelfType(): void
    {
        $factory = new Factory($this->resourceFactory, $this->hydratorFactory);
        $newResourceFactory = $this->createMock(ResourceFactoryInterface::class);
        
        $result = $factory->withResourceFactory($newResourceFactory);

        $this->assertInstanceOf(Factory::class, $result);
    }

    public function testOriginalFactoryUnchangedAfterWithResourceFactory(): void
    {
        $originalFactory = new Factory($this->resourceFactory, $this->hydratorFactory);
        $newResourceFactory = $this->createMock(ResourceFactoryInterface::class);
        
        // Create an unserializer in the original factory
        $originalUnserializer = $originalFactory->createUnserializerForClass(FakeUserClass::class);
        
        // Create new factory
        $originalFactory->withResourceFactory($newResourceFactory);
        
        $this->assertSame($originalUnserializer, $originalFactory->createUnserializerForClass(FakeUserClass::class));
    }

    public function testCreateUnserializerWithNamespacedClass(): void
    {
        $factory = new Factory($this->resourceFactory, $this->hydratorFactory);
        $unserializer = $factory->createUnserializerForClass('App\\Models\\User');

        $this->assertInstanceOf(UnserializerInterface::class, $unserializer);
    }

    public function testCreateUnserializerWithBuiltInClass(): void
    {
        $factory = new Factory($this->resourceFactory, $this->hydratorFactory);
        $unserializer = $factory->createUnserializerForClass(\DateTime::class);

        $this->assertInstanceOf(UnserializerInterface::class, $unserializer);
    }
}

