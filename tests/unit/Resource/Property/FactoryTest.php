<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property;

use Hermiod\Attribute\Constraint\ArrayConstraintInterface;
use Hermiod\Attribute\Constraint\NumberConstraintInterface;
use Hermiod\Attribute\Constraint\ObjectKeyConstraintInterface;
use Hermiod\Attribute\Constraint\ObjectValueConstraintInterface;
use Hermiod\Attribute\Constraint\StringConstraintInterface;
use Hermiod\Resource\Constraint\FactoryInterface as ConstraintFactory;
use Hermiod\Resource\FactoryInterface as ResourceFactory;
use Hermiod\Resource\Property\ArrayProperty;
use Hermiod\Resource\Property\BooleanProperty;
use Hermiod\Resource\Property\ClassProperty;
use Hermiod\Resource\Property\DateTimeInterfaceProperty;
use Hermiod\Resource\Property\Exception\UnsupportedPropertyTypeException;
use Hermiod\Resource\Property\Factory;
use Hermiod\Resource\Property\FloatProperty;
use Hermiod\Resource\Property\IntegerProperty;
use Hermiod\Resource\Property\MixedProperty;
use Hermiod\Resource\Property\ObjectProperty;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Property\Resolver\ResolverInterface;
use Hermiod\Resource\Property\StringProperty;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Factory::class)]
final class FactoryTest extends TestCase
{
    private ConstraintFactory & MockObject $constraints;

    private ResourceFactory & MockObject $resources;

    private ResolverInterface & MockObject $resolver;

    private Factory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraints = $this->createConstraintFactoryMock();
        $this->resources = $this->createResourceFactoryMock();
        $this->resolver = $this->createResolverMock();

        $this->factory = new Factory($this->constraints, $this->resources, $this->resolver);
    }

    public function testCanGetInterfaceResolver(): void
    {
        $resolver = $this->createResolverMock();
        $factory = new Factory($this->constraints, $this->resources, $resolver);

        $this->assertSame($resolver, $factory->getInterfaceResolver());
    }

    public function testCreatePropertyFromReflectionPropertyThrowsExceptionForUnsupportedBuiltinType(): void
    {
        $this->expectException(UnsupportedPropertyTypeException::class);
        $this->expectExceptionMessage("No factory is available for the PHP type 'resource'");

        $type = $this->createReflectionNamedTypeMock('resource', true);
        $reflection = $this->createReflectionPropertyMock('testProp', $type);

        $this->factory->createPropertyFromReflectionProperty($reflection);
    }

    #[DataProvider('builtInTypesWithoutDefaultProvider')]
    public function testCreatePropertyFromReflectionPropertyForBuiltInTypesWithoutDefaultValue(
        string $name,
        string $class,
        bool $nullable,
    ): void
    {
        $type = $this->createReflectionNamedTypeMock($name, true, $nullable);

        $reflection = $this->createReflectionPropertyMock('prop', $type, hasDefault: false);

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(PropertyInterface::class, $property);
        $this->assertInstanceOf($class, $property);

        $this->assertSame('prop', $property->getPropertyName());

        $this->assertFalse($property->hasDefaultValue());
    }

    #[DataProvider('builtInTypesWithDefaultProvider')]
    public function testCreatePropertyFromReflectionPropertyForBuiltInTypesWithDefaultValue(
        string $name,
        string $class,
        bool $nullable,
        mixed $value
    ): void
    {
        $type = $this->createReflectionNamedTypeMock($name, true, $nullable);
        $reflection = $this->createReflectionPropertyMock('prop', $type, hasDefault: true, defaultValue: $value);

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(PropertyInterface::class, $property);
        $this->assertInstanceOf($class, $property);

        $this->assertSame('prop', $property->getPropertyName());
        $this->assertSame($value, $property->getDefaultValue());

        $this->assertTrue($property->hasDefaultValue());
    }

    #[DataProvider('classTypesWithoutDefaultProvider')]
    public function testCreatePropertyFromReflectionPropertyForClassTypesWithoutDefaultValue(
        string $name,
        string $class,
        bool $nullable
    ): void
    {
        $type = $this->createReflectionNamedTypeMock($name, false, $nullable);

        $reflection = $this->createReflectionPropertyMock('prop', $type, hasDefault: false);

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(PropertyInterface::class, $property);
        $this->assertInstanceOf($class, $property);

        $this->assertSame('prop', $property->getPropertyName());

        $this->assertFalse($property->hasDefaultValue());
    }

    #[DataProvider('classTypesWithDefaultProvider')]
    public function testCreatePropertyFromReflectionPropertyForClassTypesWithDefaultValue(
        string $name,
        string $class,
        bool $nullable
    ): void
    {
        $type = $this->createReflectionNamedTypeMock($name, false, $nullable);

        $reflection = $this->createReflectionPropertyMock('prop', $type, hasDefault: true, defaultValue: null);

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(PropertyInterface::class, $property);
        $this->assertInstanceOf($class, $property);

        $this->assertSame('prop', $property->getPropertyName());

        $this->assertNull($property->getDefaultValue());

        $this->assertTrue($property->hasDefaultValue());
    }

    public function testCreatePropertyFromReflectionPropertyForClassPropertySetsClassName(): void
    {
        $type = $this->createReflectionNamedTypeMock(\get_class(self::createCustomClass()), false);
        $reflection = $this->createReflectionPropertyMock('prop', $type);

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(ClassProperty::class, $property);
    }

    public function testCreatePropertyFromReflectionPropertyForPropertyWithNoType(): void
    {
        $reflection = $this->createReflectionPropertyMock('prop', null);

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(PropertyInterface::class, $property);
        $this->assertInstanceOf(MixedProperty::class, $property);
        $this->assertSame('prop', $property->getPropertyName());
        $this->assertFalse($property->hasDefaultValue());
    }

    public function testCreatePropertyFromReflectionPropertyForUnionType(): void
    {
        $reflection = $this->createReflectionPropertyMock('prop', $this->createReflectionUnionTypeMock());

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(PropertyInterface::class, $property);
        $this->assertInstanceOf(StringProperty::class, $property);
    }

    public function testCreatePropertyFromReflectionPropertyForIntersectionType(): void
    {
        $reflection = $this->createReflectionPropertyMock('prop', $this->createReflectionIntersectionTypeMock());

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(PropertyInterface::class, $property);
        $this->assertInstanceOf(StringProperty::class, $property);
    }

    #[TestWith(['default' => null])]
    #[TestWith(['default' => 'foo'])]
    #[TestWith(['default' => ['this should not be possible']])]
    public function testCreatePropertyFromReflectionPropertyLoadsStringConstraints(mixed $default): void
    {
        $constraint = $this->createMock(StringConstraintInterface::class);
        $attribute = $this->createReflectionAttributeMock(StringConstraintInterface::class, []);

        $this->constraints
            ->method('createConstraint')
            ->with(StringConstraintInterface::class, [])
            ->willReturn($constraint);

        $type = $this->createReflectionNamedTypeMock('string');

        // Has no attributes
        $reflection = $this->createReflectionPropertyMock('prop', $type, $default !== null, $default);

        $reflection
            ->method('getAttributes')
            ->with(StringConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF)
            ->willReturn([]);

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(StringProperty::class, $property);

        // Has attributes
        $reflection = $this->createReflectionPropertyMock('prop', $type, $default !== null, $default);

        $reflection
            ->method('getAttributes')
            ->with(StringConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF)
            ->willReturn([$attribute]);

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(StringProperty::class, $property);
    }

    #[TestWith(['default' => null])]
    #[TestWith(['default' => 42])]
    #[TestWith(['default' => 'impossible type'])]
    public function testCreatePropertyFromReflectionPropertyLoadsNumberConstraintsForInt(mixed $default): void
    {
        $constraint = $this->createMock(NumberConstraintInterface::class);
        $attribute = $this->createReflectionAttributeMock(NumberConstraintInterface::class, []);

        $this->constraints
            ->expects($this->once())
            ->method('createConstraint')
            ->willReturn($constraint);

        $type = $this->createReflectionNamedTypeMock('int');

        // Has no attributes
        $reflection = $this->createReflectionPropertyMock('prop', $type, $default !== null, $default);

        $reflection
            ->method('getAttributes')
            ->willReturn([]);

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(IntegerProperty::class, $property);

        // Has attributes
        $reflection = $this->createReflectionPropertyMock('prop', $type, $default !== null, $default);

        $reflection
            ->method('getAttributes')
            ->willReturnMap([
                [NumberConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF, [$attribute]],
            ]);

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(IntegerProperty::class, $property);
    }

    #[TestWith(['default' => null])]
    #[TestWith(['default' => 42.24])]
    #[TestWith(['default' => 42])]
    #[TestWith(['default' => 'impossible type'])]
    public function testCreatePropertyFromReflectionPropertyLoadsNumberConstraintsForFloat(mixed $default): void
    {
        $constraint = $this->createMock(NumberConstraintInterface::class);
        $attribute = $this->createReflectionAttributeMock(NumberConstraintInterface::class, []);

        $this->constraints
            ->expects($this->once())
            ->method('createConstraint')
            ->willReturn($constraint);

        $type = $this->createReflectionNamedTypeMock('float');

        // Has no attributes
        $reflection = $this->createReflectionPropertyMock('prop', $type, $default !== null, $default);

        $reflection
            ->method('getAttributes')
            ->willReturn([]);

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(FloatProperty::class, $property);

        // Has attributes
        $reflection = $this->createReflectionPropertyMock('prop', $type, $default !== null, $default);

        $reflection
            ->method('getAttributes')
            ->willReturnMap([
                [NumberConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF, [$attribute]],
            ]);

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(FloatProperty::class, $property);
    }

    #[TestWith(['default' => null])]
    #[TestWith(['default' => ['foo']])]
    #[TestWith(['default' => 'impossible type'])]
    public function testCreatePropertyFromReflectionPropertyLoadsArrayConstraints(mixed $default): void
    {
        $constraint = $this->createMock(ArrayConstraintInterface::class);
        $attribute = $this->createReflectionAttributeMock(ArrayConstraintInterface::class, []);

        $this->constraints->expects($this->once())->method('createConstraint')->willReturn($constraint);

        $type = $this->createReflectionNamedTypeMock('array');

        // Has no attributes
        $reflection = $this->createReflectionPropertyMock('prop', $type, $default !== null, $default);

        $reflection->method('getAttributes')
            ->with(ArrayConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF)
            ->willReturn([]);

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(ArrayProperty::class, $property);

        // Has attributes
        $reflection = $this->createReflectionPropertyMock('prop', $type, $default !== null, $default);

        $reflection->method('getAttributes')
            ->with(ArrayConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF)
            ->willReturn([$attribute]);

        $property = $this->factory->createPropertyFromReflectionProperty($reflection);

        $this->assertInstanceOf(ArrayProperty::class, $property);
    }

    public function testCreatePropertyFromReflectionPropertyLoadsObjectConstraints(): void
    {
        $keyConstraint = $this->createMock(ObjectKeyConstraintInterface::class);
        $valueConstraint = $this->createMock(ObjectValueConstraintInterface::class);

        $keyAttribute = $this->createReflectionAttributeMock(ObjectKeyConstraintInterface::class, []);
        $valueAttribute = $this->createReflectionAttributeMock(ObjectValueConstraintInterface::class, []);

        $this->constraints
            ->method('createConstraint')
            ->willReturnMap([
                [ObjectKeyConstraintInterface::class, [], $keyConstraint],
                [ObjectValueConstraintInterface::class, [], $valueConstraint],
            ]);

        $type = $this->createReflectionNamedTypeMock('object');

        $combinations = [
            [
                [ObjectKeyConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF, []],
                [ObjectValueConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF, []],
            ],
            [
                [ObjectKeyConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF, [$keyAttribute]],
                [ObjectValueConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF, []],
            ],
            [
                [ObjectKeyConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF, []],
                [ObjectValueConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF, [$valueAttribute]],
            ],
            [
                [ObjectKeyConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF, [$keyAttribute]],
                [ObjectValueConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF, [$valueAttribute]],
            ],
        ];

        foreach ($combinations as $combination) {
            $reflection = $this->createReflectionPropertyMock('prop', $type);

            $reflection
                ->method('getAttributes')
                ->willReturnMap($combination);

            $property = $this->factory->createPropertyFromReflectionProperty($reflection);

            $this->assertInstanceOf(ObjectProperty::class, $property);
        }
    }

    public function testAllAttributesAreLoaded(): void
    {
        $constraint = $this->createMock(StringConstraintInterface::class);

        $this->constraints
            ->expects($this->exactly(3))
            ->method('createConstraint')
            ->willReturn($constraint);

        $type = $this->createReflectionNamedTypeMock('string');

        $reflection = $this->createReflectionPropertyMock('prop', $type);

        $reflection
            ->method('getAttributes')
            ->with(StringConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF)
            ->willReturn([
                $this->createReflectionAttributeMock(\uniqid(StringConstraintInterface::class)),
                $this->createReflectionAttributeMock(\uniqid(StringConstraintInterface::class)),
                $this->createReflectionAttributeMock(\uniqid(StringConstraintInterface::class)),
            ]);

        $this->factory->createPropertyFromReflectionProperty($reflection);
    }

    public function testRepeatedAttributesResultInMultipleSubFactoryCalls(): void
    {
        $constraint = $this->createMock(StringConstraintInterface::class);
        $attribute = $this->createReflectionAttributeMock(\uniqid(StringConstraintInterface::class));

        $this->constraints
            ->expects($this->exactly(4))
            ->method('createConstraint')
            ->willReturn($constraint);

        $type = $this->createReflectionNamedTypeMock('string');

        $reflection = $this->createReflectionPropertyMock('prop', $type);

        $reflection
            ->method('getAttributes')
            ->with(StringConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF)
            ->willReturn([$attribute, $attribute, $attribute, $attribute]);

        $this->factory->createPropertyFromReflectionProperty($reflection);
    }

    public static function builtInTypesWithoutDefaultProvider(): \Generator
    {
        yield 'array, not-nullable' => ['array', ArrayProperty::class, false];
        yield 'array, nullable' => ['array', ArrayProperty::class, true];
        yield 'bool, not-nullable' => ['bool', BooleanProperty::class, false];
        yield 'bool, nullable' => ['bool', BooleanProperty::class, true];
        yield 'float, not-nullable' => ['float', FloatProperty::class, false];
        yield 'float, nullable' => ['float', FloatProperty::class, true];
        yield 'int, not-nullable' => ['int', IntegerProperty::class, false];
        yield 'int, nullable' => ['int', IntegerProperty::class, true];
        yield 'object, not-nullable' => ['object', ObjectProperty::class, false];
        yield 'object, nullable' => ['object', ObjectProperty::class, true];
        yield 'string, not-nullable' => ['string', StringProperty::class, false];
        yield 'string, nullable' => ['string', StringProperty::class, true];
        yield 'mixed' => ['mixed', MixedProperty::class, true];
    }

    public static function builtInTypesWithDefaultProvider(): \Generator
    {
        yield 'array, nullable, with-default' => ['array', ArrayProperty::class, true, ['a' => 1]];
        yield 'bool, not-nullable, with-default' => ['bool', BooleanProperty::class, false, true];
        yield 'bool, nullable, with-default' => ['bool', BooleanProperty::class, true, false];
        yield 'float, not-nullable, with-default' => ['float', FloatProperty::class, false, 1.23];
        yield 'float, nullable, with-default-int' => ['float', FloatProperty::class, true, 5.0];
        yield 'int, not-nullable, with-default' => ['int', IntegerProperty::class, false, 42];
        yield 'string, not-nullable, with-default' => ['string', StringProperty::class, false, 'hello'];
        yield 'mixed, with-default-string' => ['mixed', MixedProperty::class, true, 'a string'];
        yield 'mixed, with-default-null' => ['mixed', MixedProperty::class, true, null];
    }

    public static function classTypesWithoutDefaultProvider(): \Generator
    {
        yield 'DateTimeInterface, not-nullable' => [\DateTimeInterface::class, DateTimeInterfaceProperty::class, false];
        yield 'DateTimeInterface, nullable' => [\DateTimeInterface::class, DateTimeInterfaceProperty::class, true];
        yield 'DateTime, not-nullable' => [\DateTime::class, DateTimeInterfaceProperty::class, false];
        yield 'DateTime, nullable' => [\DateTime::class, DateTimeInterfaceProperty::class, true];
        yield 'DateTimeImmutable, not-nullable' => [\DateTimeImmutable::class, DateTimeInterfaceProperty::class, false];
        yield 'DateTimeImmutable, nullable' => [\DateTimeImmutable::class, DateTimeInterfaceProperty::class, true];
        yield 'CustomClass, not-nullable' => [\get_class(self::createCustomClass()), ClassProperty::class, false];
        yield 'CustomClass, nullable' => [\get_class(self::createCustomClass()), ClassProperty::class, true];
    }

    public static function classTypesWithDefaultProvider(): \Generator
    {
        yield 'DateTimeInterface, nullable, with-default-null' => [\DateTimeInterface::class, DateTimeInterfaceProperty::class, true];
        yield 'DateTime, nullable, with-default-null' => [\DateTime::class, DateTimeInterfaceProperty::class, true];
        yield 'DateTimeImmutable, nullable, with-default-null' => [\DateTimeImmutable::class, DateTimeInterfaceProperty::class, true];
        yield 'CustomClass, nullable, with-default-null' => [\get_class(self::createCustomClass()), ClassProperty::class, true];
    }

    private function createConstraintFactoryMock(): ConstraintFactory & MockObject
    {
        return $this->createMock(ConstraintFactory::class);
    }

    private function createResourceFactoryMock(): ResourceFactory & MockObject
    {
        return $this->createMock(ResourceFactory::class);
    }

    private function createResolverMock(): ResolverInterface & MockObject
    {
        return $this->createMock(ResolverInterface::class);
    }

    private function createReflectionPropertyMock(
        string $name,
        ?\ReflectionType $type,
        bool $hasDefault = false,
        mixed $defaultValue = null
    ): \ReflectionProperty & MockObject
    {
        $mock = $this->createMock(\ReflectionProperty::class);
        $mock->method('getName')->willReturn($name);
        $mock->method('getType')->willReturn($type);
        $mock->method('hasDefaultValue')->willReturn($hasDefault);
        $mock->method('hasDefaultValue')->willReturn($hasDefault);

        if ($hasDefault) {
            $mock->method('getDefaultValue')->willReturn($defaultValue);
        }

        return $mock;
    }

    private function createReflectionNamedTypeMock(
        string $name,
        bool $isBuiltIn = true,
        bool $allowsNull = true,
    ): \ReflectionNamedType & MockObject
    {
        $mock = $this->createMock(\ReflectionNamedType::class);

        $mock
            ->method('getName')
            ->willReturn($name);

        $mock
            ->method('isBuiltin')
            ->willReturn($isBuiltIn);

        $mock
            ->method('allowsNull')
            ->willReturn($allowsNull);

        return $mock;
    }

    private function createReflectionUnionTypeMock(): \ReflectionUnionType & MockObject
    {
        return $this->createMock(\ReflectionUnionType::class);
    }

    private function createReflectionIntersectionTypeMock(): \ReflectionIntersectionType & MockObject
    {
        return $this->createMock(\ReflectionIntersectionType::class);
    }

    private function createReflectionAttributeMock(
        string $name,
        array $arguments = [],
    ): \ReflectionAttribute & MockObject
    {
        $mock = $this->createMock(\ReflectionAttribute::class);

        $mock
            ->method('getName')
            ->willReturn($name);

        $mock
            ->method('getArguments')
            ->willReturn($arguments);

        return $mock;
    }

    private static function createCustomClass(): object
    {
        return new class {};
    }
}
