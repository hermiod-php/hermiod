<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ArrayValueNumberInList;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayValueNumberInList::class)]
final class ArrayValueNumberInListTest extends TestCase
{
    #[DataProvider('provideMatchingValues')]
    public function testAllowsValuesInList(mixed $value): void
    {
        $constraint = new ArrayValueNumberInList(1, 2, 3.5);

        $this->assertTrue(
            $constraint->mapValueMatchesConstraint($value),
            'Expected value to match list constraint'
        );
    }

    #[DataProvider('provideNonMatchingValues')]
    public function testRejectsValuesNotInList(mixed $value): void
    {
        $constraint = new ArrayValueNumberInList(1, 2, 3.5);

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected value to fail list constraint'
        );
    }

    public function testReturnsExplanationForInvalidValue(): void
    {
        $constraint = new ArrayValueNumberInList(10, 20);
        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.data');

        $message = $constraint->getMismatchExplanation($path, 15);

        $this->assertSame(
            '$.data must be one of [10, 20] but 15 given',
            $message,
            'Expected mismatch explanation to match'
        );
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function provideMatchingValues(): array
    {
        return [
            'first value in list' => [1],
            'second value in list' => [2],
            'float value in list' => [3.5],
        ];
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function provideNonMatchingValues(): array
    {
        return [
            'not in list' => [4],
            'float close to match' => [3.499],
            'string version of match' => ['2'],
            'null' => [null],
        ];
    }
}
