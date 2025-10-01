<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ArrayValueStringInList;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayValueStringInList::class)]
final class ArrayValueStringInListTest extends TestCase
{
    #[DataProvider('provideMatchingValues')]
    public function testAllowsValuesThatExistInList(mixed $value): void
    {
        $constraint = new ArrayValueStringInList('apple', 'banana', 'cherry');

        $this->assertTrue(
            $constraint->mapValueMatchesConstraint($value),
            'Expected value to be accepted when found in the list'
        );
    }

    #[DataProvider('provideNonMatchingValues')]
    public function testRejectsValuesNotInList(mixed $value): void
    {
        $constraint = new ArrayValueStringInList('apple', 'banana', 'cherry');

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected value to be rejected when not found in the list'
        );
    }

    public function testReturnsExplanationForInvalidValue(): void
    {
        $constraint = new ArrayValueStringInList('red', 'blue');
        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.colour');

        $message = $constraint->getMismatchExplanation($path, 'green');

        $this->assertSame(
            "$.colour must be one of ['red', 'blue'] but 'green' given",
            $message,
            'Expected mismatch explanation to match'
        );
    }

    #[DataProvider('nonStringErrorMessageValueProvider')]
    public function testCanFormatErrorWithNonStringValue(mixed $value, string $type): void
    {
        $constraint = new ArrayValueStringInList('red');
        $path = $this->createMock(PathInterface::class);

        $this->assertStringContainsString(
            "but $type given",
            $constraint->getMismatchExplanation($path, $value),
        );
    }

    public static function nonStringErrorMessageValueProvider(): array
    {
        return [
            'integer' => [42, 'int'],
            'float' => [3.14, 'float'],
            'boolean true' => [true, 'bool'],
            'boolean false' => [false, 'bool'],
            'null' => [null, 'null'],
            'array' => [[1, 2, 3], 'array'],
            'object' => [new \stdClass(), 'object'],
        ];
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function provideMatchingValues(): array
    {
        return [
            'first in list' => ['apple'],
            'middle of list' => ['banana'],
            'last in list' => ['cherry'],
        ];
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function provideNonMatchingValues(): array
    {
        return [
            'not in list' => ['orange'],
            'wrong case' => ['Apple'],
            'number instead of string' => [123],
            'null' => [null],
        ];
    }
}
