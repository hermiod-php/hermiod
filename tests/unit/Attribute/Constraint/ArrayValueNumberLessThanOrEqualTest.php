<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ArrayValueNumberLessThanOrEqual;
use Hermiod\Attribute\Constraint\Traits\MapValueNumberLessThanOrEqual;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayValueNumberLessThanOrEqual::class)]
#[CoversClass(MapValueNumberLessThanOrEqual::class)]
final class ArrayValueNumberLessThanOrEqualTest extends TestCase
{
    #[DataProvider('provideMatchingValues')]
    public function testAllowsValuesLessThanOrEqualToThreshold(mixed $value): void
    {
        $constraint = new ArrayValueNumberLessThanOrEqual(10);

        $this->assertTrue(
            $constraint->mapValueMatchesConstraint($value),
            'Expected value to be accepted when less than or equal to threshold'
        );
    }

    #[DataProvider('provideNonMatchingValues')]
    public function testRejectsValuesGreaterThanThreshold(mixed $value): void
    {
        $constraint = new ArrayValueNumberLessThanOrEqual(10);

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected value to be rejected when greater than threshold'
        );
    }

    public function testReturnsExplanationForInvalidValue(): void
    {
        $constraint = new ArrayValueNumberLessThanOrEqual(5);
        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.count');

        $message = $constraint->getMismatchExplanation($path, 9);

        $this->assertSame(
            '$.count must be a number less than or equal to 5 but 9 given',
            $message,
            'Expected mismatch explanation to match'
        );

        $message = $constraint->getMismatchExplanation($path, 9.1);

        $this->assertSame(
            '$.count must be a number less than or equal to 5 but 9.1 given',
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
            'equal to threshold' => [10],
            'less than threshold' => [9],
            'float less than threshold' => [9.9],
            'float equal to threshold' => [10.0],
        ];
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function provideNonMatchingValues(): array
    {
        return [
            'just over threshold' => [10.1],
            'greater than threshold' => [11],
            'string value' => ['10'],
            'null' => [null],
        ];
    }
}
