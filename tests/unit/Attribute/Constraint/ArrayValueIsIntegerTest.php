<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ArrayConstraintInterface;
use Hermiod\Attribute\Constraint\ArrayValueIsInteger;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayValueIsInteger::class)]
final class ArrayValueIsIntegerTest extends TestCase
{
    public function testImplementsArrayConstraintInterface(): void
    {
        $constraint = new ArrayValueIsInteger();

        $this->assertInstanceOf(ArrayConstraintInterface::class, $constraint, 'Must implement ArrayConstraintInterface');
    }

    #[DataProvider('provideValidValues')]
    public function testValidValuesPassConstraint(mixed $value): void
    {
        $constraint = new ArrayValueIsInteger();

        $this->assertTrue($constraint->mapValueMatchesConstraint($value), 'Expected integer value to pass constraint');
    }

    #[DataProvider('provideInvalidValues')]
    public function testInvalidValuesFailConstraint(mixed $value): void
    {
        $constraint = new ArrayValueIsInteger();

        $this->assertFalse($constraint->mapValueMatchesConstraint($value), 'Expected non-integer value to fail constraint');
    }

    public function testMismatchExplanationIncludesJsonPathAndType(): void
    {
        $constraint = new ArrayValueIsInteger();
        $path = $this->createMockPath('$.foo.bar[3]');

        $explanation = $constraint->getMismatchExplanation($path, 3.14);

        $this->assertStringContainsString('$.foo.bar[3]', $explanation, 'Expected explanation to include JSONPath');
        $this->assertStringContainsString('must be an integer', $explanation, 'Expected message to mention integer requirement');
        $this->assertStringContainsString('double given', $explanation, 'Expected message to include actual type');
    }

    // 👇 Private helper
    private function createMockPath(string $string): PathInterface & \PHPUnit\Framework\MockObject\MockObject
    {
        $mock = $this->createMock(PathInterface::class);
        $mock->method('__toString')->willReturn($string);

        return $mock;
    }

    // 👇 Data Providers
    public static function provideValidValues(): \Generator
    {
        yield 'zero' => [0];
        yield 'positive int' => [42];
        yield 'negative int' => [-17];
        yield 'max int' => [PHP_INT_MAX];
        yield 'min int' => [PHP_INT_MIN];
    }

    public static function provideInvalidValues(): \Generator
    {
        yield 'float' => [3.14];
        yield 'string' => ['42'];
        yield 'null' => [null];
        yield 'bool true' => [true];
        yield 'bool false' => [false];
        yield 'array' => [[1]];
        yield 'object' => [(object)['val' => 1]];
        yield 'callable' => [fn () => 1];
    }
}
