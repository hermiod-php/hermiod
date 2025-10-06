<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource;

use Hermiod\Attribute\ResourceInterface as Options;
use Hermiod\Exception\TooMuchRecursionException;
use Hermiod\Json\FragmentInterface;
use Hermiod\Resource\Hydrator\HydratorInterface;
use Hermiod\Resource\Name\StrategyInterface;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Resource\Property\CollectionInterface;
use Hermiod\Resource\Property\FactoryInterface;
use Hermiod\Resource\Property\PrimitiveInterface;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\Validation\ResultInterface;
use Hermiod\Resource\Resource;
use Hermiod\Resource\ResourceInterface;
use Hermiod\Resource\RuntimeResolverInterface;
use Hermiod\Tests\Unit\Fakes\FakeEmptyClass;
use Hermiod\Tests\Unit\Fakes\FakeResourceProperty;
use Hermiod\Tests\Unit\Fakes\FakeResourcePropertyWithChildren;
use Hermiod\Tests\Unit\Fakes\FakeRuntimeResolverProperty;
use Hermiod\Tests\Unit\Fakes\FakeValidationResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Resource::class)]
#[Medium]
class ResourceTest extends TestCase
{
    public function testImplementsResourceInterface(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $class = new class {
            public string $name;
            private int $age;
            protected bool $active;
        };

        $resource = new Resource($class::class, $factory, $naming, $options);

        $this->assertInstanceOf(ResourceInterface::class, $resource);
    }

    public function testGetClassNameReturnsCorrectClassName(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $class = new class {
            public string $name;
        };

        $resource = new Resource($class::class, $factory, $naming, $options);

        $this->assertSame($class::class, $resource->getClassName());
    }

    public function testConstructorThrowsExceptionForNonExistentClass(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $class = 'NonExistentClass';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Class $class does not exist");

        new Resource($class, $factory, $naming, $options);
    }

    #[DataProvider('validClassNameProvider')]
    public function testConstructorWithVariousValidClassNames(string $class): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();

        $resource = new Resource($class, $factory, $naming, $options);

        $this->assertSame($class, $resource->getClassName());
    }

    public function testGetPropertiesReturnsCollectionInterface(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $property = $this->createProperty();
        $filter = \ReflectionProperty::IS_PUBLIC;
        $class = new class {
            public string $name;
        };

        $options
            ->expects($this->once())
            ->method('getReflectionPropertyFilter')
            ->willReturn($filter);

        $factory
            ->expects($this->once())
            ->method('createPropertyFromReflectionProperty')
            ->willReturn($property);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->getProperties();

        $this->assertInstanceOf(CollectionInterface::class, $result);
    }

    public function testGetPropertiesCachesResult(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $property = $this->createProperty();
        $filter = \ReflectionProperty::IS_PUBLIC;
        $class = new class {
            public string $name;
        };

        $options
            ->expects($this->once())
            ->method('getReflectionPropertyFilter')
            ->willReturn($filter);

        $factory
            ->expects($this->once())
            ->method('createPropertyFromReflectionProperty')
            ->willReturn($property);

        $resource = new Resource($class::class, $factory, $naming, $options);

        $first = $resource->getProperties();
        $second = $resource->getProperties();

        $this->assertSame($first, $second);
    }

    public function testCanAutomaticallySerialiseReturnsBooleanFromOptions(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $expected = true;
        $class = new class {
            public string $name;
        };

        $options
            ->expects($this->once())
            ->method('canAutoSerialize')
            ->willReturn($expected);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->canAutomaticallySerialise();

        $this->assertSame($expected, $result);
    }

    public function testValidateAndTransposeWithValidData(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $data = ['name' => 'test'];
        $property = $this->createProperty();
        $filter = \ReflectionProperty::IS_PUBLIC;

        $class = new class {
            public string $name;
        };

        $this->setupOptionsForGetProperties($options, $filter);
        $this->setupFactoryForGetProperties($factory, $property);
        $this->setupPropertyForValidation($property, $naming, 'name', true);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->validateAndTranspose($path, $data);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testValidateAndTransposeWithObjectData(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $data = (object) ['name' => 'test'];
        $property = $this->createProperty();
        $filter = \ReflectionProperty::IS_PUBLIC;
        $class = new class {
            public string $name;
        };

        $this->setupOptionsForGetProperties($options, $filter);
        $this->setupFactoryForGetProperties($factory, $property);
        $this->setupPropertyForValidation($property, $naming, 'name', true);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->validateAndTranspose($path, $data);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testValidateAndTransposeWithMissingRequiredProperty(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $pathWithKey = $this->createPath();
        $data = [];
        $property = $this->createProperty();
        $filter = \ReflectionProperty::IS_PUBLIC;
        $class = new class {
            public string $name;
        };

        $this->setupOptionsForGetProperties($options, $filter);
        $this->setupFactoryForGetProperties($factory, $property);

        $naming
            ->expects($this->once())
            ->method('format')
            ->with('testProperty')
            ->willReturn('test_property');

        $path
            ->expects($this->once())
            ->method('withObjectKey')
            ->with('test_property')
            ->willReturn($pathWithKey);

        $pathWithKey
            ->expects($this->once())
            ->method('__toString')
            ->willReturn('$.test_property');

        $property
            ->expects($this->atLeastOnce())
            ->method('getPropertyName')
            ->willReturn('testProperty');

        $property
            ->expects($this->once())
            ->method('hasDefaultValue')
            ->willReturn(false);

        $property
            ->expects($this->once())
            ->method('isNullable')
            ->willReturn(false);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->validateAndTranspose($path, $data);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testValidateAndTransposeWithDefaultValue(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $data = [];
        $property = $this->createProperty();
        $filter = \ReflectionProperty::IS_PUBLIC;
        $defaultValue = 'default';
        $class = new class {
            public string $name;
        };

        $this->setupOptionsForGetProperties($options, $filter);
        $this->setupFactoryForGetProperties($factory, $property);

        $naming
            ->expects($this->once())
            ->method('format')
            ->with('testProperty')
            ->willReturn('test_property');

        $property
            ->expects($this->atLeastOnce())
            ->method('getPropertyName')
            ->willReturn('testProperty');

        $property
            ->expects($this->once())
            ->method('hasDefaultValue')
            ->willReturn(true);

        $property
            ->expects($this->once())
            ->method('getDefaultValue')
            ->willReturn($defaultValue);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $resource->validateAndTranspose($path, $data);

        $this->assertSame($defaultValue, $data['test_property']);
    }

    public function testValidateAndTransposeWithNullableProperty(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $data = [];
        $property = $this->createProperty();
        $filter = \ReflectionProperty::IS_PUBLIC;
        $class = new class {
            public string $name;
        };

        $this->setupOptionsForGetProperties($options, $filter);
        $this->setupFactoryForGetProperties($factory, $property);

        $naming
            ->expects($this->once())
            ->method('format')
            ->with('testProperty')
            ->willReturn('test_property');

        $property
            ->expects($this->atLeastOnce())
            ->method('getPropertyName')
            ->willReturn('testProperty');

        $property
            ->expects($this->once())
            ->method('hasDefaultValue')
            ->willReturn(false);

        $property
            ->expects($this->once())
            ->method('isNullable')
            ->willReturn(true);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $resource->validateAndTranspose($path, $data);

        $this->assertNull($data['test_property']);
    }

    public function testValidateAndTransposeWithPrimitiveProperty(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $pathWithKey = $this->createPath();
        $data = ['test_property' => 'value'];
        $validationResult = $this->createValidationResult();
        $filter = \ReflectionProperty::IS_PUBLIC;

        $class = new class {
            public string $name;
        };

        $this->setupOptionsForGetProperties($options, $filter);

        $property = $this->createMockForIntersectionOfInterfaces([PropertyInterface::class, PrimitiveInterface::class]);

        $factory
            ->expects($this->once())
            ->method('createPropertyFromReflectionProperty')
            ->willReturn($property);

        $naming
            ->expects($this->once())
            ->method('format')
            ->with('testProperty')
            ->willReturn('test_property');

        $path
            ->expects($this->once())
            ->method('withObjectKey')
            ->with('test_property')
            ->willReturn($pathWithKey);

        $property
            ->expects($this->atLeastOnce())
            ->method('getPropertyName')
            ->willReturn('testProperty');

        $property
            ->expects($this->atLeastOnce())
            ->method('normalisePhpValue')
            ->willReturnCallback(fn ($value) => $value);

        $property
            ->expects($this->once())
            ->method('checkValueAgainstConstraints')
            ->with($pathWithKey, 'value')
            ->willReturn($validationResult);

        $validationResult
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $resource->validateAndTranspose($path, $data);

        $this->assertTrue(true);
    }

    public function testValidateAndTransposeWithInvalidConstraints(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $pathWithKey = $this->createPath();
        $data = ['test_property' => 'invalid_value'];
        $property = $this->createProperty();
        $validationResult = $this->createValidationResult();
        $filter = \ReflectionProperty::IS_PUBLIC;
        $errors = ['Validation error'];

        $class = new class {
            public string $name;
        };

        $this->setupOptionsForGetProperties($options, $filter);
        $this->setupFactoryForGetProperties($factory, $property);

        $naming
            ->expects($this->once())
            ->method('format')
            ->with('testProperty')
            ->willReturn('test_property');

        $path
            ->expects($this->once())
            ->method('withObjectKey')
            ->with('test_property')
            ->willReturn($pathWithKey);

        $property
            ->expects($this->atLeastOnce())
            ->method('getPropertyName')
            ->willReturn('testProperty');

        $property
            ->expects($this->once())
            ->method('checkValueAgainstConstraints')
            ->with($pathWithKey, 'invalid_value')
            ->willReturn($validationResult);

        $validationResult
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(false);

        $validationResult
            ->expects($this->once())
            ->method('getValidationErrors')
            ->willReturn($errors);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->validateAndTranspose($path, $data);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testValidateAndTransposeWithResourceProperty(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $pathWithKey = $this->createPath();
        $data = ['test_property' => ['nested' => 'value']];
        $property = $this->createProperty();
        $validationResult = $this->createValidationResult();
        $filter = \ReflectionProperty::IS_PUBLIC;

        $class = new class {
            public string $name;
            private int $age;
            protected bool $active;
        };

        $this->setupOptionsForGetProperties($options, $filter);
        $this->setupFactoryForGetProperties($factory, $property);

        $naming
            ->expects($this->once())
            ->method('format')
            ->with('testProperty')
            ->willReturn('test_property');

        $path
            ->expects($this->once())
            ->method('withObjectKey')
            ->with('test_property')
            ->willReturn($pathWithKey);

        $property
            ->expects($this->atLeastOnce())
            ->method('getPropertyName')
            ->willReturn('testProperty');

        $property
            ->expects($this->once())
            ->method('checkValueAgainstConstraints')
            ->willReturn($validationResult);

        $validationResult
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->validateAndTranspose($path, $data);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testValidateAndTransposeWithActualResourceProperty(): void
    {
        // Reset the static depth that might have been set by previous tests
        $reflection = new \ReflectionClass(Resource::class);
        $depthProperty = $reflection->getProperty('depth');
        $depthProperty->setAccessible(true);
        $depthProperty->setValue(null, 0);

        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $pathWithKey = $this->createPath();
        $data = ['test_property' => ['nested' => 'value']];
        $filter = \ReflectionProperty::IS_PUBLIC;
        $class = new class {
            public string $name;
        };

        $this->setupOptionsForGetProperties($options, $filter);

        $resourceProperty = new FakeResourceProperty();

        $factory
            ->expects($this->once())
            ->method('createPropertyFromReflectionProperty')
            ->willReturn($resourceProperty);

        $naming
            ->expects($this->once())
            ->method('format')
            ->with('testProperty')
            ->willReturn('test_property');

        $path
            ->expects($this->once())
            ->method('withObjectKey')
            ->with('test_property')
            ->willReturn($pathWithKey);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->validateAndTranspose($path, $data);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testValidateAndTransposeWithActualRuntimeResolverProperty(): void
    {
        // Reset the static depth that might have been set by previous tests
        $reflection = new \ReflectionClass(Resource::class);
        $depthProperty = $reflection->getProperty('depth');
        $depthProperty->setAccessible(true);
        $depthProperty->setValue(null, 0);

        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $pathWithKey = $this->createPath();
        $data = ['test_property' => ['type' => 'specific', 'value' => 'test']];
        $filter = \ReflectionProperty::IS_PUBLIC;
        $class = new class {
            public string $name;
        };

        $this->setupOptionsForGetProperties($options, $filter);

        $runtimeProperty = new FakeRuntimeResolverProperty();

        $factory
            ->expects($this->once())
            ->method('createPropertyFromReflectionProperty')
            ->willReturn($runtimeProperty);

        $naming
            ->expects($this->once())
            ->method('format')
            ->with('testProperty')
            ->willReturn('test_property');

        $path
            ->expects($this->once())
            ->method('withObjectKey')
            ->with('test_property')
            ->willReturn($pathWithKey);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->validateAndTranspose($path, $data);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testValidateAndTransposeThrowsExceptionWhenMaxRecursionReached(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $data = ['test_property' => ['nested' => 'value']];
        $filter = \ReflectionProperty::IS_PUBLIC;
        $class = new class {
            public string $name;
        };

        // Set the static depth to the limit before creating the resource
        $reflection = new \ReflectionClass(Resource::class);
        $depthProperty = $reflection->getProperty('depth');
        $depthProperty->setAccessible(true);
        $depthProperty->setValue(null, 128); // At the limit

        $this->setupOptionsForGetProperties($options, $filter);

        $property = $this->createProperty();

        $factory
            ->expects($this->once())
            ->method('createPropertyFromReflectionProperty')
            ->willReturn($property);

        $resource = new Resource($class::class, $factory, $naming, $options);

        $this->expectException(TooMuchRecursionException::class);

        try {
            $resource->validateAndTranspose($path, $data);
        } finally {
            // Reset the depth after the test to avoid affecting other tests
            $depthProperty->setValue(null, 0);
        }
    }

    public function testValidateAndTransposeRecursiveHydration(): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $filter = \ReflectionProperty::IS_PUBLIC;
        $path = $this->createMock(PathInterface::class);

        $naming
            ->expects($this->once())
            ->method('format')
            ->with('empty')
            ->willReturn('empty');

        $path
            ->method('withObjectKey')
            ->willReturn($path);

        $class = new class {
            public FakeEmptyClass $empty;
        };

        $this->setupOptionsForGetProperties($options, $filter);

        $property = $this->createMockForIntersectionOfInterfaces([PropertyInterface::class, ResourceInterface::class]);

        $property
            ->method('getClassName')
            ->willReturn(FakeEmptyClass::class);

        $property
            ->method('checkValueAgainstConstraints')
            ->willReturnCallback(function (): ResultInterface {
                $mock = $this->createMock(ResultInterface::class);

                $mock
                    ->method('isValid')
                    ->willReturn(true);

                return $mock;
            });

        $property
            ->expects($this->atLeastOnce())
            ->method('getPropertyName')
            ->willReturn('empty');

        $factory
            ->expects($this->once())
            ->method('createPropertyFromReflectionProperty')
            ->willReturn($property);

        $resource = new Resource($class::class, $factory, $naming, $options);

        $data = ['empty' => ['foo' => 'bar']];

        $result = $resource->validateAndTranspose(
            $path,
            $data
        );

        $hydrator = $this->createMock(HydratorInterface::class);

        $hydrator
            ->expects($this->once())
            ->method('hydrate')
            ->willReturnCallback(function ($class, $data) {
                $this->assertSame(FakeEmptyClass::class, $class);
                $this->assertIsArray($data);

                return new FakeEmptyClass();
            });

        $result->hydrate($hydrator);
    }

    public function testValidateAndTransposeWithNonArrayNonObjectData(): void
    {
        // Reset the static depth that might have been set by previous tests
        $reflection = new \ReflectionClass(Resource::class);
        $depthProperty = $reflection->getProperty('depth');
        $depthProperty->setAccessible(true);
        $depthProperty->setValue(null, 0);

        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $pathWithKey = $this->createPath();
        $data = ['test_property' => 'simple_string'];
        $property = $this->createProperty();
        $validationResult = $this->createValidationResult();
        $filter = \ReflectionProperty::IS_PUBLIC;
        $class = new class {
            public string $name;
        };

        $this->setupOptionsForGetProperties($options, $filter);
        $this->setupFactoryForGetProperties($factory, $property);

        $naming
            ->expects($this->once())
            ->method('format')
            ->with('testProperty')
            ->willReturn('test_property');

        $path
            ->expects($this->once())
            ->method('withObjectKey')
            ->with('test_property')
            ->willReturn($pathWithKey);

        $property
            ->expects($this->atLeastOnce())
            ->method('getPropertyName')
            ->willReturn('testProperty');

        $property
            ->expects($this->once())
            ->method('checkValueAgainstConstraints')
            ->with($pathWithKey, 'simple_string')
            ->willReturn($validationResult);

        $validationResult
            ->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->validateAndTranspose($path, $data);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testValidateAndTransposeWithResourcePropertyAndArrayData(): void
    {
        // Reset the static depth that might have been set by previous tests
        $reflection = new \ReflectionClass(Resource::class);
        $depthProperty = $reflection->getProperty('depth');
        $depthProperty->setAccessible(true);
        $depthProperty->setValue(null, 0);

        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $pathWithKey = $this->createPath();
        $data = ['test_property' => ['nested' => 'value']];
        $filter = \ReflectionProperty::IS_PUBLIC;
        $class = new class {
            public string $name;
        };

        $this->setupOptionsForGetProperties($options, $filter);

        $resourceProperty = new FakeResourceProperty();

        $factory
            ->expects($this->once())
            ->method('createPropertyFromReflectionProperty')
            ->willReturn($resourceProperty);

        $naming
            ->expects($this->once())
            ->method('format')
            ->with('testProperty')
            ->willReturn('test_property');

        $path
            ->expects($this->once())
            ->method('withObjectKey')
            ->with('test_property')
            ->willReturn($pathWithKey);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->validateAndTranspose($path, $data);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    #[DataProvider('propertyFilterProvider')]
    public function testGetPropertiesWithDifferentFilters(int $filter): void
    {
        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $property = $this->createProperty();
        $class = new class {
            public string $name;
            private int $age;
            protected bool $active;
        };

        $options
            ->expects($this->once())
            ->method('getReflectionPropertyFilter')
            ->willReturn($filter);

        $factory
            ->expects($this->atLeastOnce())
            ->method('createPropertyFromReflectionProperty')
            ->willReturn($property);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->getProperties();

        $this->assertInstanceOf(CollectionInterface::class, $result);
    }

    public static function propertyFilterProvider(): array
    {
        return [
            'public properties' => [\ReflectionProperty::IS_PUBLIC],
            'private properties' => [\ReflectionProperty::IS_PRIVATE],
            'protected properties' => [\ReflectionProperty::IS_PROTECTED],
            'public and private' => [\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PRIVATE],
        ];
    }

    public static function validClassNameProvider(): array
    {
        return [
            'stdClass' => [\stdClass::class],
            'DateTime' => [\DateTime::class],
            'Exception' => [\Exception::class],
            'ArrayIterator' => [\ArrayIterator::class],
        ];
    }

    private function setupOptionsForGetProperties(Options|MockObject $options, int $filter): void
    {
        $options
            ->expects($this->once())
            ->method('getReflectionPropertyFilter')
            ->willReturn($filter);
    }

    private function setupFactoryForGetProperties(FactoryInterface|MockObject $factory, PropertyInterface|MockObject $property): void
    {
        $factory
            ->expects($this->once())
            ->method('createPropertyFromReflectionProperty')
            ->willReturn($property);
    }

    private function setupPropertyForValidation(PropertyInterface|MockObject $property, StrategyInterface|MockObject $naming, string $propertyName, bool $hasData): void
    {
        $property
            ->expects($this->atLeastOnce())
            ->method('getPropertyName')
            ->willReturn($propertyName);

        $naming
            ->expects($this->once())
            ->method('format')
            ->with($propertyName)
            ->willReturn($propertyName);

        if ($hasData) {
            $validationResult = $this->createValidationResult();
            $property
                ->expects($this->once())
                ->method('checkValueAgainstConstraints')
                ->willReturn($validationResult);

            $validationResult
                ->expects($this->once())
                ->method('isValid')
                ->willReturn(true);
        }
    }

    private function createPropertyFactory(): FactoryInterface|MockObject
    {
        return $this->createMock(FactoryInterface::class);
    }

    private function createNamingStrategy(): StrategyInterface|MockObject
    {
        return $this->createMock(StrategyInterface::class);
    }

    private function createOptions(): Options|MockObject
    {
        return $this->createMock(Options::class);
    }

    private function createProperty(): PropertyInterface|MockObject
    {
        return $this->createMock(PropertyInterface::class);
    }

    private function createPath(): PathInterface|MockObject
    {
        return $this->createMock(PathInterface::class);
    }

    private function createValidationResult(): ResultInterface|MockObject
    {
        return $this->createMock(ResultInterface::class);
    }

    public function testValidateAndTransposeWithResourcePropertyHavingNestedProperties(): void
    {
        // Reset the static depth that might have been set by previous tests
        $reflection = new \ReflectionClass(Resource::class);
        $depthProperty = $reflection->getProperty('depth');
        $depthProperty->setAccessible(true);
        $depthProperty->setValue(null, 0);

        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $pathWithKey = $this->createPath();
        $data = ['test_property' => ['nested' => 'value', 'another' => 'test']];
        $filter = \ReflectionProperty::IS_PUBLIC;
        $class = new class {
            public string $name;
        };

        $this->setupOptionsForGetProperties($options, $filter);

        // Use a resource property that has children to trigger line 158 (recurse call)
        $resourceProperty = new FakeResourcePropertyWithChildren();

        $factory
            ->expects($this->once())
            ->method('createPropertyFromReflectionProperty')
            ->willReturn($resourceProperty);

        // Allow multiple calls to format since recursion will process nested properties
        $naming
            ->expects($this->atLeastOnce())
            ->method('format')
            ->willReturnCallback(function ($propertyName) {
                return match ($propertyName) {
                    'testProperty' => 'test_property',
                    'fakeProperty' => 'fake_property',
                    default => $propertyName,
                };
            });

        $path
            ->expects($this->once())
            ->method('withObjectKey')
            ->with('test_property')
            ->willReturn($pathWithKey);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->validateAndTranspose($path, $data);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testValidateAndTransposeWithRuntimeResolverTriggersConcreteResource(): void
    {
        // Reset the static depth that might have been set by previous tests
        $reflection = new \ReflectionClass(Resource::class);
        $depthProperty = $reflection->getProperty('depth');
        $depthProperty->setAccessible(true);
        $depthProperty->setValue(null, 0);

        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $pathWithKey = $this->createPath();
        $data = ['test_property' => ['type' => 'specific', 'value' => 'test', 'nested' => 'data']];
        $filter = \ReflectionProperty::IS_PUBLIC;
        $class = new class {
            public string $name;
        };

        $this->setupOptionsForGetProperties($options, $filter);

        // Create a runtime resolver that returns a resource with children to trigger line 158
        $runtimeProperty = new class implements PropertyInterface, RuntimeResolverInterface {
            public function getPropertyName(): string { return 'testProperty'; }
            public function hasDefaultValue(): bool { return false; }
            public function getDefaultValue(): mixed { return null; }
            public function isNullable(): bool { return false; }
            public function checkValueAgainstConstraints(PathInterface $path, mixed $value): ResultInterface {
                return new FakeValidationResult();
            }
            public function normaliseJsonValue(mixed $value): mixed { return $value; }

            // This method triggers lines 142-144
            public function getConcreteResource(array $fragment): ResourceInterface {
                return new FakeResourcePropertyWithChildren();
            }
        };

        $factory
            ->expects($this->once())
            ->method('createPropertyFromReflectionProperty')
            ->willReturn($runtimeProperty);

        // Allow multiple calls to format since recursion will process nested properties
        $naming
            ->expects($this->atLeastOnce())
            ->method('format')
            ->willReturnCallback(function ($propertyName) {
                return match ($propertyName) {
                    'testProperty' => 'test_property',
                    'fakeProperty' => 'fake_property',
                    default => $propertyName,
                };
            });

        $path
            ->expects($this->once())
            ->method('withObjectKey')
            ->with('test_property')
            ->willReturn($pathWithKey);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->validateAndTranspose($path, $data);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }

    public function testValidateAndTransposeTriggersRecursiveCallOnResourceInterface(): void
    {
        // Reset the static depth that might have been set by previous tests
        $reflection = new \ReflectionClass(Resource::class);
        $depthProperty = $reflection->getProperty('depth');
        $depthProperty->setAccessible(true);
        $depthProperty->setValue(null, 0);

        $factory = $this->createPropertyFactory();
        $naming = $this->createNamingStrategy();
        $options = $this->createOptions();
        $path = $this->createPath();
        $pathWithKey = $this->createPath();
        $data = ['test_property' => ['nested_prop' => 'value']];
        $filter = \ReflectionProperty::IS_PUBLIC;
        $class = new class {
            public string $name;
        };

        $this->setupOptionsForGetProperties($options, $filter);

        // Create a resource property that has nested properties to trigger line 158 (recurse call)
        $resourceProperty = new FakeResourcePropertyWithChildren();

        $factory
            ->expects($this->once())
            ->method('createPropertyFromReflectionProperty')
            ->willReturn($resourceProperty);

        // Allow multiple calls to format since recursion will process nested properties
        $naming
            ->expects($this->atLeastOnce())
            ->method('format')
            ->willReturnCallback(function ($propertyName) {
                return match ($propertyName) {
                    'testProperty' => 'test_property',
                    'fakeProperty' => 'fake_property',
                    default => $propertyName,
                };
            });

        $path
            ->expects($this->once())
            ->method('withObjectKey')
            ->with('test_property')
            ->willReturn($pathWithKey);

        $resource = new Resource($class::class, $factory, $naming, $options);
        $result = $resource->validateAndTranspose($path, $data);

        $this->assertInstanceOf(ResultInterface::class, $result);
    }
}
