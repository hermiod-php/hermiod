<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Constraint;

use Hermiod\Attribute\Constraint\ConstraintInterface;
use Hermiod\Resource\Constraint\CachedFactory;
use Hermiod\Resource\Constraint\Exception\ClassIsNotConstraintException;
use Hermiod\Resource\Constraint\Exception\MissingConstraintClassException;
use Hermiod\Resource\Constraint\FactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CachedFactory::class)]
class CachedFactoryTest extends TestCase
{
    private CachedFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new CachedFactory();
    }

    public function testImplementsFactoryInterface(): void
    {
        $this->assertInstanceOf(FactoryInterface::class, $this->factory);
    }

    public function testCreateConstraintWithValidConstraintClass(): void
    {
        $testConstraintClass = new class implements ConstraintInterface {};

        $constraint = $this->factory->createConstraint(\get_class($testConstraintClass));

        $this->assertInstanceOf(\get_class($testConstraintClass), $constraint);
        $this->assertInstanceOf(ConstraintInterface::class, $constraint);
    }

    public function testCreateConstraintWithArguments(): void
    {
        $testConstraintClass = new class implements ConstraintInterface {
            private array $arguments;

            public function __construct(...$arguments)
            {
                $this->arguments = $arguments;
            }

            public function getArguments(): array
            {
                return $this->arguments;
            }
        };

        $arguments = ['arg1', 'arg2', 123];
        $constraint = $this->factory->createConstraint(\get_class($testConstraintClass), $arguments);

        $this->assertInstanceOf(\get_class($testConstraintClass), $constraint);
        $this->assertSame($arguments, $constraint->getArguments());
    }

    public function testCreateConstraintCachesInstances(): void
    {
        $testConstraintClass = new class implements ConstraintInterface {};
        $className = \get_class($testConstraintClass);

        $constraint1 = $this->factory->createConstraint($className);
        $constraint2 = $this->factory->createConstraint($className);

        $this->assertSame($constraint1, $constraint2);
    }

    public function testCreateConstraintCachesInstancesWithArguments(): void
    {
        $testConstraintClass = new class implements ConstraintInterface {
            private array $arguments;

            public function __construct(...$arguments)
            {
                $this->arguments = $arguments;
            }

            public function getArguments(): array
            {
                return $this->arguments;
            }
        };

        $className = \get_class($testConstraintClass);
        $arguments = ['test', 123];
        $constraint1 = $this->factory->createConstraint($className, $arguments);
        $constraint2 = $this->factory->createConstraint($className, $arguments);

        $this->assertSame($constraint1, $constraint2);
    }

    public function testCreateConstraintDifferentArgumentsCreateDifferentInstances(): void
    {
        $testConstraintClass = new class implements ConstraintInterface {
            private array $arguments;

            public function __construct(...$arguments)
            {
                $this->arguments = $arguments;
            }

            public function getArguments(): array
            {
                return $this->arguments;
            }
        };

        $className = \get_class($testConstraintClass);
        $arguments1 = ['test1'];
        $arguments2 = ['test2'];

        $constraint1 = $this->factory->createConstraint($className, $arguments1);
        $constraint2 = $this->factory->createConstraint($className, $arguments2);

        $this->assertNotSame($constraint1, $constraint2);
        $this->assertSame($arguments1, $constraint1->getArguments());
        $this->assertSame($arguments2, $constraint2->getArguments());
    }

    public function testCreateConstraintDifferentClassesCreateDifferentInstances(): void
    {
        $testConstraintClass1 = new class implements ConstraintInterface {};
        $testConstraintClass2 = new class implements ConstraintInterface {};

        $constraint1 = $this->factory->createConstraint(\get_class($testConstraintClass1));
        $constraint2 = $this->factory->createConstraint(\get_class($testConstraintClass2));

        $this->assertNotSame($constraint1, $constraint2);
        $this->assertInstanceOf(\get_class($testConstraintClass1), $constraint1);
        $this->assertInstanceOf(\get_class($testConstraintClass2), $constraint2);
    }

    public function testCreateConstraintWithEmptyArgumentsArray(): void
    {
        $testConstraintClass = new class implements ConstraintInterface {};
        $className = \get_class($testConstraintClass);

        $constraint1 = $this->factory->createConstraint($className, []);
        $constraint2 = $this->factory->createConstraint($className);

        $this->assertSame($constraint1, $constraint2);
    }

    public function testCreateConstraintWithComplexArguments(): void
    {
        $testConstraintClass = new class implements ConstraintInterface {
            private array $arguments;

            public function __construct(...$arguments)
            {
                $this->arguments = $arguments;
            }

            public function getArguments(): array
            {
                return $this->arguments;
            }
        };

        $complexArgs = [
            'string' => 'test',
            'array' => [1, 2, 3],
            'array-arrays' => [[1], [2], [3, [1, 2]]],
            'int' => 42,
            'object' => new \stdClass(),
            'null' => null,
            'bool' => true,
            'float' => 3.14
        ];

        $className = \get_class($testConstraintClass);
        $constraint1 = $this->factory->createConstraint($className, $complexArgs);
        $constraint2 = $this->factory->createConstraint($className, $complexArgs);

        $this->assertSame($constraint1, $constraint2);
        $this->assertSame($complexArgs, $constraint1->getArguments());
    }

    public function testCreateConstraintThrowsExceptionForNonExistentClass(): void
    {
        $this->expectException(MissingConstraintClassException::class);
        $this->expectExceptionMessage('Unable to load constraint class NonExistentClass');

        $this->factory->createConstraint('NonExistentClass');
    }

    public function testCreateConstraintThrowsExceptionForClassNotImplementingConstraintInterface(): void
    {
        $this->expectException(ClassIsNotConstraintException::class);
        $this->expectExceptionMessage('The class ' . \stdClass::class . ' does not implement ' . ConstraintInterface::class);

        $this->factory->createConstraint(\stdClass::class);
    }

    public function testCreateConstraintThrowsExceptionForNonConstraintClass(): void
    {
        $nonConstraintClass = new class {};

        $this->expectException(ClassIsNotConstraintException::class);
        $this->expectExceptionMessage('The class ' . \get_class($nonConstraintClass) . ' does not implement ' . ConstraintInterface::class);

        $this->factory->createConstraint(\get_class($nonConstraintClass));
    }

    public function testCacheKeyGenerationWithSerializedArguments(): void
    {
        $testConstraintClass = new class implements ConstraintInterface {
            private array $arguments;

            public function __construct(...$arguments)
            {
                $this->arguments = $arguments;
            }

            public function getArguments(): array
            {
                return $this->arguments;
            }
        };

        // Test that arguments are properly serialized for cache key generation
        $className = \get_class($testConstraintClass);
        $args1 = ['a' => 1, 'b' => 2];
        $args2 = ['b' => 2, 'a' => 1]; // Different order, same content

        $constraint1 = $this->factory->createConstraint($className, $args1);
        $constraint2 = $this->factory->createConstraint($className, $args2);

        // These should be different instances because array order matters in serialization
        $this->assertNotSame($constraint1, $constraint2);
    }

    public function testCreateConstraintWithNestedArrayArguments(): void
    {
        $testConstraintClass = new class implements ConstraintInterface {
            private array $arguments;

            public function __construct(...$arguments)
            {
                $this->arguments = $arguments;
            }

            public function getArguments(): array
            {
                return $this->arguments;
            }
        };

        $nestedArgs = [
            'level1' => [
                'level2' => [
                    'level3' => 'deep value'
                ]
            ]
        ];

        $className = \get_class($testConstraintClass);
        $constraint1 = $this->factory->createConstraint($className, $nestedArgs);
        $constraint2 = $this->factory->createConstraint($className, $nestedArgs);

        $this->assertSame($constraint1, $constraint2);
        $this->assertSame($nestedArgs, $constraint1->getArguments());
    }

    public function testCreateConstraintCacheIsolatesArgumentVariations(): void
    {
        $testConstraintClass = new class implements ConstraintInterface {
            private array $arguments;

            public function __construct(...$arguments)
            {
                $this->arguments = $arguments;
            }

            public function getArguments(): array
            {
                return $this->arguments;
            }
        };

        // Create constraints with various argument combinations to ensure proper isolation
        $className = \get_class($testConstraintClass);
        $constraint1 = $this->factory->createConstraint($className, ['a']);
        $constraint2 = $this->factory->createConstraint($className, ['b']);
        $constraint3 = $this->factory->createConstraint($className, ['a', 'b']);
        $constraint4 = $this->factory->createConstraint($className, ['a']);

        $this->assertNotSame($constraint1, $constraint2);
        $this->assertNotSame($constraint1, $constraint3);
        $this->assertNotSame($constraint2, $constraint3);
        $this->assertSame($constraint1, $constraint4); // Same as first
    }

    public function testCreateConstraintWithObjectArguments(): void
    {
        $testConstraintClass = new class implements ConstraintInterface {
            private array $arguments;

            public function __construct(...$arguments)
            {
                $this->arguments = $arguments;
            }

            public function getArguments(): array
            {
                return $this->arguments;
            }
        };

        $object = new \stdClass();
        $object->property = 'value';

        $className = \get_class($testConstraintClass);
        $constraint1 = $this->factory->createConstraint($className, [$object]);
        $constraint2 = $this->factory->createConstraint($className, [$object]);

        $this->assertSame($constraint1, $constraint2);
    }

    public function testCreateConstraintPerformanceWithMultipleCalls(): void
    {
        $testConstraintClass = new class implements ConstraintInterface {};

        // Verify that cache actually improves performance by avoiding reconstruction
        $startTime = microtime(true);

        // First call creates the constraint
        $className = \get_class($testConstraintClass);
        $constraint1 = $this->factory->createConstraint($className);

        // Subsequent calls should use cache
        for ($i = 0; $i < 100; $i++) {
            $constraint = $this->factory->createConstraint($className);
            $this->assertSame($constraint1, $constraint);
        }

        $endTime = microtime(true);

        // Test should complete quickly if caching is working
        $this->assertLessThan(0.1, $endTime - $startTime);
    }
}
