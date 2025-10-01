<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ArrayValueNumberLessThan;
use Hermiod\Attribute\Constraint\Traits\MapValueNumberLessThan;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayValueNumberLessThan::class)]
#[CoversClass(MapValueNumberLessThan::class)]
final class ArrayValueNumberLessThanTest extends TestCase
{
    #[DataProvider('provideMatchingValues')]
    public function testAllowsValuesLessThanTarget(mixed $value): void
    {
        $constraint = new ArrayValueNumberLessThan(10);

        $this->assertTrue(
            $constraint->mapValueMatchesConstraint($value),
            'Expected value to match constraint'
        );
    }

    #[DataProvider('provideNonMatchingValues')]
    public function testRejectsValuesNotLessThanTarget(mixed $value): void
    {
        $constraint = new ArrayValueNumberLessThan(10);

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected value to fail constraint'
        );
    }

    public function testReturnsExplanationForInvalidValue(): void
    {
        $constraint = new ArrayValueNumberLessThan(5);
        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.items[2]');

        $message = $constraint->getMismatchExplanation($path, 8);

        $this->assertSame(
            '$.items[2] must be a number less than 5 but 8 given',
            $message,
            'Expected mismatch explanation to match'
        );

        $message = $constraint->getMismatchExplanation($path, 8.1);

        $this->assertSame(
            '$.items[2] must be a number less than 5 but 8.1 given',
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
            'less than target int' => [5],
            'float less than target' => [9.9],
            'zero' => [0],
            'negative number' => [-100],
        ];
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function provideNonMatchingValues(): array
    {
        return [
            'equal to target' => [10],
            'greater than target' => [11],
            'float equal to target' => [10.0],
            'string input' => ['9'],
            'null input' => [null],
        ];
    }
}
