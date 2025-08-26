<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Constraint\Exception;

use Hermiod\Resource\Constraint\Exception\Exception;
use Hermiod\Resource\Constraint\Exception\MissingConstraintClassException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MissingConstraintClassException::class)]
class MissingConstraintClassExceptionTest extends TestCase
{
    public function testExtendsInvalidArgumentException(): void
    {
        $exception = MissingConstraintClassException::new('TestClass');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }

    public function testImplementsExceptionInterface(): void
    {
        $exception = MissingConstraintClassException::new('TestClass');

        $this->assertInstanceOf(Exception::class, $exception);
    }

    #[DataProvider('classNameProvider')]
    public function testNewCreatesExceptionWithCorrectMessage(string $className): void
    {
        $exception = MissingConstraintClassException::new($className);

        $expectedMessage = \sprintf(
            'Unable to load constraint class %s',
            $className
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    #[DataProvider('classNameProvider')]
    public function testNewReturnsNewInstanceEachTime(string $className): void
    {
        $exception1 = MissingConstraintClassException::new($className);
        $exception2 = MissingConstraintClassException::new($className);

        $this->assertNotSame($exception1, $exception2);
        $this->assertEquals($exception1->getMessage(), $exception2->getMessage());
    }

    public function testExceptionWithNonExistentClass(): void
    {
        $className = 'NonExistentClass';
        $exception = MissingConstraintClassException::new($className);

        $expectedMessage = \sprintf(
            'Unable to load constraint class %s',
            $className
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testExceptionWithNamespacedNonExistentClass(): void
    {
        $className = 'App\\Constraints\\NonExistentConstraint';
        $exception = MissingConstraintClassException::new($className);

        $expectedMessage = \sprintf(
            'Unable to load constraint class %s',
            $className
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionWithTypicalConstraintClassName(): void
    {
        $className = 'App\\Constraints\\EmailValidationConstraint';
        $exception = MissingConstraintClassException::new($className);

        $expectedMessage = \sprintf(
            'Unable to load constraint class %s',
            $className
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionWithEmptyString(): void
    {
        $exception = MissingConstraintClassException::new('');

        $expectedMessage = \sprintf(
            'Unable to load constraint class %s',
            ''
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionWithSpecialCharacters(): void
    {
        $className = 'Constraint\\With\\Special\\Characters';
        $exception = MissingConstraintClassException::new($className);

        $expectedMessage = \sprintf(
            'Unable to load constraint class %s',
            $className
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionWithInvalidClassName(): void
    {
        $className = 'Invalid-Class-Name';
        $exception = MissingConstraintClassException::new($className);

        $expectedMessage = \sprintf(
            'Unable to load constraint class %s',
            $className
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionWithVeryLongClassName(): void
    {
        $className = str_repeat('VeryLongConstraintClassName', 10);
        $exception = MissingConstraintClassException::new($className);

        $expectedMessage = \sprintf(
            'Unable to load constraint class %s',
            $className
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionIsThrowable(): void
    {
        $exception = MissingConstraintClassException::new('TestClass');

        $this->assertInstanceOf(\Throwable::class, $exception);
        
        // Test that it can be thrown and caught
        $this->expectException(MissingConstraintClassException::class);
        throw $exception;
    }

    public function testExceptionInheritanceChain(): void
    {
        $exception = MissingConstraintClassException::new('TestClass');

        // Test the complete inheritance chain
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(MissingConstraintClassException::class, $exception);
    }

    public function testExceptionWithClassNameContainingNumbers(): void
    {
        $className = 'Constraint123WithNumbers456';
        $exception = MissingConstraintClassException::new($className);

        $expectedMessage = \sprintf(
            'Unable to load constraint class %s',
            $className
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionWithUnderscoreClassName(): void
    {
        $className = 'My_Custom_Constraint_Class';
        $exception = MissingConstraintClassException::new($className);

        $expectedMessage = \sprintf(
            'Unable to load constraint class %s',
            $className
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionMessageFormatting(): void
    {
        $className = 'TestConstraint';
        $exception = MissingConstraintClassException::new($className);
        
        // Verify the message format exactly matches the expected pattern
        $message = $exception->getMessage();
        $this->assertStringStartsWith('Unable to load constraint class ', $message);
        $this->assertStringEndsWith($className, $message);
        $this->assertStringContainsString($className, $message);
    }

    public static function classNameProvider(): array
    {
        return [
            'simple_class' => ['TestConstraint'],
            'namespaced_class' => ['App\\Constraints\\TestConstraint'],
            'deeply_namespaced_class' => ['Very\\Deep\\Namespace\\Path\\TestConstraint'],
            'class_with_numbers' => ['Test123Constraint'],
            'class_with_underscores' => ['Test_Constraint_Name'],
            'single_letter_class' => ['A'],
            'constraint_suffix' => ['EmailValidationConstraint'],
            'validation_suffix' => ['EmailValidation'],
            'short_name' => ['Max'],
            'camel_case' => ['EmailValidationConstraint'],
            'pascal_case' => ['EmailValidationConstraint'],
            'all_caps' => ['EMAILVALIDATION'],
            'mixed_case' => ['eMaIlVaLiDaTiOn'],
        ];
    }
}
