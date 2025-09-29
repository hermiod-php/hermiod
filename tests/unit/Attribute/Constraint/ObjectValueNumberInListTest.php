<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ObjectValueNumberInList;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectValueNumberInList::class)]
final class ObjectValueNumberInListTest extends TestCase
{
    #[DataProvider('provideValidValues')]
    public function testValidValuesAreAccepted(int|float $value): void
    {
        $constraint = new ObjectValueNumberInList(10, 20, 30.5);

        $this->assertTrue($constraint->mapValueMatchesConstraint($value));
    }

    #[DataProvider('provideInvalidValues')]
    public function testInvalidValuesAreRejected(int|float $value): void
    {
        $constraint = new ObjectValueNumberInList(10, 20, 30.5);

        $this->assertFalse($constraint->mapValueMatchesConstraint($value));
    }

    #[DataProvider('provideInvalidTypes')]
    public function testInvalidTypesAreRejected(mixed $value): void
    {
        $constraint = new ObjectValueNumberInList(10, 20, 30.5);

        $this->assertFalse($constraint->mapValueMatchesConstraint($value));
    }

    public function testMismatchExplanationIncludesDetails(): void
    {
        $constraint = new ObjectValueNumberInList(10, 20, 30.5);

        $path = $this->createMock(PathInterface::class);

        $path->method('__toString')->willReturn('$.value');

        $message = $constraint->getMismatchExplanation($path, 42);

        $this->assertStringContainsString('$.value', $message);
        $this->assertStringContainsString('one of', $message);
        $this->assertStringContainsString('10', $message);
        $this->assertStringContainsString('30.5', $message);
        $this->assertStringContainsString('42', $message);
    }

    public static function provideValidValues(): \Generator
    {
        yield 'first' => [10];
        yield 'middle' => [20];
        yield 'last float' => [30.5];
    }

    public static function provideInvalidValues(): \Generator
    {
        yield 'missing int' => [15];
        yield 'near float' => [30.0];
        yield 'negative' => [-10];
        yield 'zero' => [0];
    }

    public static function provideInvalidTypes(): \Generator
    {
        yield 'string number' => ['10'];
        yield 'bool' => [true];
        yield 'array' => [[10]];
        yield 'object' => [new \stdClass()];
        yield 'null' => [null];
    }
}

