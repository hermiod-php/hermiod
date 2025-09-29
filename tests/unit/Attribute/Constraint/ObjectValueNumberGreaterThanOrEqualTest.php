<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ObjectValueNumberGreaterThanOrEqual;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectValueNumberGreaterThanOrEqual::class)]
final class ObjectValueNumberGreaterThanOrEqualTest extends TestCase
{
    #[DataProvider('provideValidValues')]
    public function testValidValuesAreAccepted(mixed $value): void
    {
        $constraint = new ObjectValueNumberGreaterThanOrEqual(5);

        $this->assertTrue($constraint->mapValueMatchesConstraint($value));
    }

    #[DataProvider('provideInvalidNumericValues')]
    public function testInvalidNumericValuesAreRejected(mixed $value): void
    {
        $constraint = new ObjectValueNumberGreaterThanOrEqual(5);

        $this->assertFalse($constraint->mapValueMatchesConstraint($value));
    }

    #[DataProvider('provideInvalidTypeValues')]
    public function testInvalidTypesAreRejected(mixed $value): void
    {
        $constraint = new ObjectValueNumberGreaterThanOrEqual(5);

        $this->assertFalse($constraint->mapValueMatchesConstraint($value));
    }

    public function testMismatchExplanationIncludesDetails(): void
    {
        $constraint = new ObjectValueNumberGreaterThanOrEqual(5);

        $path = $this->createMock(PathInterface::class);

        $path->method('__toString')->willReturn('$.value');

        $message = $constraint->getMismatchExplanation($path, 4);

        $this->assertStringContainsString('$.value', $message);
        $this->assertStringContainsString('4', $message);
        $this->assertStringContainsString('5', $message);
        $this->assertStringContainsString('greater than or equal', $message);
    }

    public static function provideValidValues(): \Generator
    {
        yield 'equal int' => [5];
        yield 'greater int' => [6];
        yield 'greater float' => [5.01];
    }

    public static function provideInvalidNumericValues(): \Generator
    {
        yield 'less int' => [4];
        yield 'less float' => [4.99];
    }

    public static function provideInvalidTypeValues(): \Generator
    {
        yield 'string' => ['6'];
        yield 'bool' => [true];
        yield 'array' => [[6]];
        yield 'object' => [new \stdClass()];
        yield 'null' => [null];
    }
}

