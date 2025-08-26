<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Constraint\Exception;

use Hermiod\Attribute\Constraint\ConstraintInterface;
use Hermiod\Resource\Constraint\Exception\ClassIsNotConstraintException;
use Hermiod\Resource\Constraint\Exception\Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClassIsNotConstraintException::class)]
class ClassIsNotConstraintExceptionTest extends TestCase
{
    public function testExtendsInvalidArgumentException(): void
    {
        $exception = ClassIsNotConstraintException::new('TestClass');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }

    public function testImplementsExceptionInterface(): void
    {
        $exception = ClassIsNotConstraintException::new('TestClass');

        $this->assertInstanceOf(Exception::class, $exception);
    }

    #[DataProvider('classNameProvider')]
    public function testNewCreatesExceptionWithCorrectMessage(string $className): void
    {
        $exception = ClassIsNotConstraintException::new($className);

        $expectedMessage = \sprintf(
            'The class %s does not implement %s',
            $className,
            ConstraintInterface::class
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    #[DataProvider('classNameProvider')]
    public function testNewReturnsNewInstanceEachTime(string $className): void
    {
        $exception1 = ClassIsNotConstraintException::new($className);
        $exception2 = ClassIsNotConstraintException::new($className);

        $this->assertNotSame($exception1, $exception2);
        $this->assertEquals($exception1->getMessage(), $exception2->getMessage());
    }

    public function testExceptionWithStandardClass(): void
    {
        $exception = ClassIsNotConstraintException::new(\stdClass::class);

        $expectedMessage = \sprintf(
            'The class %s does not implement %s',
            \stdClass::class,
            ConstraintInterface::class
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionWithBuiltInClass(): void
    {
        $exception = ClassIsNotConstraintException::new(\DateTime::class);

        $expectedMessage = \sprintf(
            'The class %s does not implement %s',
            \DateTime::class,
            ConstraintInterface::class
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionWithAnonymousClass(): void
    {
        $anonymousClass = new class {};
        $className = \get_class($anonymousClass);

        $exception = ClassIsNotConstraintException::new($className);

        $expectedMessage = \sprintf(
            'The class %s does not implement %s',
            $className,
            ConstraintInterface::class
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionWithNamespacedClass(): void
    {
        $exception = ClassIsNotConstraintException::new('App\\Services\\SomeClass');

        $expectedMessage = \sprintf(
            'The class %s does not implement %s',
            'App\\Services\\SomeClass',
            ConstraintInterface::class
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionWithEmptyString(): void
    {
        $exception = ClassIsNotConstraintException::new('');

        $expectedMessage = \sprintf(
            'The class %s does not implement %s',
            '',
            ConstraintInterface::class
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionWithSpecialCharacters(): void
    {
        $className = 'Class\\With\\Special\\Characters';
        $exception = ClassIsNotConstraintException::new($className);

        $expectedMessage = \sprintf(
            'The class %s does not implement %s',
            $className,
            ConstraintInterface::class
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionIsThrowable(): void
    {
        $exception = ClassIsNotConstraintException::new('TestClass');

        $this->assertInstanceOf(\Throwable::class, $exception);
        
        // Test that it can be thrown and caught
        $this->expectException(ClassIsNotConstraintException::class);
        throw $exception;
    }

    public function testExceptionInheritanceChain(): void
    {
        $exception = ClassIsNotConstraintException::new('TestClass');

        // Test the complete inheritance chain
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(ClassIsNotConstraintException::class, $exception);
    }

    public static function classNameProvider(): array
    {
        return [
            'simple_class' => ['TestClass'],
            'namespaced_class' => ['App\\Domain\\TestClass'],
            'deeply_namespaced_class' => ['Very\\Deep\\Namespace\\Path\\TestClass'],
            'class_with_numbers' => ['Test123Class'],
            'class_with_underscores' => ['Test_Class_Name'],
            'single_letter_class' => ['A'],
            'builtin_class' => [\stdClass::class],
            'datetime_class' => [\DateTime::class],
            'exception_class' => [\Exception::class],
        ];
    }
}
