<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ArrayConstraintInterface;
use Hermiod\Attribute\Constraint\ArrayValueIsString;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayValueIsString::class)]
final class ArrayValueIsStringTest extends TestCase
{
    public function testImplementsArrayConstraintInterface(): void
    {
        $constraint = new ArrayValueIsString();

        $this->assertInstanceOf(ArrayConstraintInterface::class, $constraint, 'Must implement ArrayConstraintInterface');
    }

    #[DataProvider('provideValidValues')]
    public function testValidValuesPassConstraint(mixed $value): void
    {
        $constraint = new ArrayValueIsString();

        $this->assertTrue($constraint->mapValueMatchesConstraint($value), 'Expected string value to pass constraint');
    }

    #[DataProvider('provideInvalidValues')]
    public function testInvalidValuesFailConstraint(mixed $value): void
    {
        $constraint = new ArrayValueIsString();

        $this->assertFalse($constraint->mapValueMatchesConstraint($value), 'Expected non-string value to fail constraint');
    }

    public function testMismatchExplanationIncludesJsonPathAndType(): void
    {
        $constraint = new ArrayValueIsString();
        $path = $this->createMockPath('$.profile.email');

        $explanation = $constraint->getMismatchExplanation($path, 123);

        $this->assertStringContainsString('$.profile.email', $explanation, 'Expected explanation to include JSONPath');
        $this->assertStringContainsString('must be a string', $explanation, 'Expected message to mention string requirement');
        $this->assertStringContainsString('int given', $explanation, 'Expected message to include actual type');
    }

    private function createMockPath(string $string): PathInterface & \PHPUnit\Framework\MockObject\MockObject
    {
        $mock = $this->createMock(PathInterface::class);
        $mock->method('__toString')->willReturn($string);

        return $mock;
    }

    public static function provideValidValues(): \Generator
    {
        yield 'empty string' => [''];
        yield 'basic string' => ['hello'];
        yield 'numeric string' => ['123'];
        yield 'whitespace string' => [" \n\t"];
        yield 'unicode string' => ['💬こんにちは'];
    }

    public static function provideInvalidValues(): \Generator
    {
        yield 'integer' => [1];
        yield 'float' => [3.14];
        yield 'bool true' => [true];
        yield 'bool false' => [false];
        yield 'null' => [null];
        yield 'array' => [['a']];
        yield 'object' => [(object)['s' => 'x']];
        yield 'callable' => [fn () => 'x'];
    }
}
