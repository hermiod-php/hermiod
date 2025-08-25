<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ArrayValueNumberGreaterThanOrEqual;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayValueNumberGreaterThanOrEqual::class)]
final class ArrayValueNumberGreaterThanOrEqualTest extends TestCase
{
    #[DataProvider('provideMatchingValues')]
    public function testAllowsValuesGreaterThanOrEqualToThreshold(mixed $value): void
    {
        $constraint = new ArrayValueNumberGreaterThanOrEqual(5);

        $this->assertTrue(
            $constraint->mapValueMatchesConstraint($value),
            'Expected value to be accepted when greater than or equal to threshold'
        );
    }

    #[DataProvider('provideNonMatchingValues')]
    public function testRejectsValuesLessThanThreshold(mixed $value): void
    {
        $constraint = new ArrayValueNumberGreaterThanOrEqual(5);

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected value to be rejected when less than threshold'
        );
    }

    public function testReturnsExplanationForInvalidValue(): void
    {
        $constraint = new ArrayValueNumberGreaterThanOrEqual(10);
        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.limit');

        $message = $constraint->getMismatchExplanation($path, 7);

        $this->assertSame(
            '$.limit must be a number greater than or equal to 10 but 7 given',
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
            'equal to threshold' => [5],
            'greater than threshold' => [6],
            'float greater than threshold' => [5.1],
            'float equal to threshold' => [5.0],
        ];
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function provideNonMatchingValues(): array
    {
        return [
            'just under threshold' => [4.9],
            'less than threshold' => [4],
            'string value' => ['5'],
            'null' => [null],
        ];
    }
}
