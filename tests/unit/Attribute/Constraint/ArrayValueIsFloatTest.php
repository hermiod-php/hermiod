<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ArrayConstraintInterface;
use Hermiod\Attribute\Constraint\ArrayValueIsFloat;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayValueIsFloat::class)]
final class ArrayValueIsFloatTest extends TestCase
{
    public function testImplementsArrayConstraintInterface(): void
    {
        $constraint = new ArrayValueIsFloat();

        $this->assertInstanceOf(ArrayConstraintInterface::class, $constraint, 'Must implement ArrayConstraintInterface');
    }

    #[DataProvider('provideValidValues')]
    public function testValidValuesPassConstraint(mixed $value): void
    {
        $constraint = new ArrayValueIsFloat();

        $this->assertTrue($constraint->mapValueMatchesConstraint($value), 'Expected value to pass float constraint');
    }

    #[DataProvider('provideInvalidValues')]
    public function testInvalidValuesFailConstraint(mixed $value): void
    {
        $constraint = new ArrayValueIsFloat();

        $this->assertFalse($constraint->mapValueMatchesConstraint($value), 'Expected value to fail float constraint');
    }

    public function testMismatchExplanationMentionsType(): void
    {
        $constraint = new ArrayValueIsFloat();
        $path = $this->createMockPath('$.some.property');

        $explanation = $constraint->getMismatchExplanation($path, 'not-a-float');

        $this->assertStringContainsString('must be an int or a float', $explanation, 'Expected message to mention float requirement');
        $this->assertStringContainsString('string given', $explanation, 'Expected message to include value type');
        $this->assertStringContainsString('$.some.property', $explanation, 'Expected message to include path');
    }

    // 👇 Private helper
    private function createMockPath(string $string): PathInterface&\PHPUnit\Framework\MockObject\MockObject
    {
        $mock = $this->createMock(PathInterface::class);
        $mock->method('__toString')->willReturn($string);

        return $mock;
    }

    // 👇 Data Providers
    public static function provideValidValues(): \Generator
    {
        yield 'float value' => [3.1415];
        yield 'integer value' => [42];
        yield 'negative int' => [-7];
        yield 'negative float' => [-2.5];
        yield 'zero' => [0];
    }

    public static function provideInvalidValues(): \Generator
    {
        yield 'string' => ['3.14'];
        yield 'null' => [null];
        yield 'bool true' => [true];
        yield 'bool false' => [false];
        yield 'array' => [[1.0]];
        yield 'object' => [(object)['a' => 1.0]];
        yield 'callable' => [fn () => 1.0];
    }
}
