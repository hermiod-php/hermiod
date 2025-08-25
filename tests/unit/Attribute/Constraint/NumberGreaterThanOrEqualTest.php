<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\NumberGreaterThanOrEqual;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NumberGreaterThanOrEqual::class)]
final class NumberGreaterThanOrEqualTest extends TestCase
{
    #[DataProvider('provideValidNumbers')]
    public function testValidNumbersPassConstraint(int|float $input): void
    {
        $constraint = new NumberGreaterThanOrEqual(10);

        $this->assertTrue(
            $constraint->valueMatchesConstraint($input),
            "Expected {$input} to be greater than or equal to 10"
        );
    }

    #[DataProvider('provideInvalidNumbers')]
    public function testInvalidNumbersFailConstraint(int|float $input): void
    {
        $constraint = new NumberGreaterThanOrEqual(10);

        $this->assertFalse(
            $constraint->valueMatchesConstraint($input),
            "Expected {$input} to not be greater than or equal to 10"
        );
    }

    #[DataProvider('provideInvalidNumbers')]
    public function testMismatchExplanationIncludesPathAndValues(int|float $input): void
    {
        $constraint = new NumberGreaterThanOrEqual(10);

        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.value');

        $message = $constraint->getMismatchExplanation($path, $input);

        $this->assertStringContainsString('$.value', $message, 'Expected path in explanation');
        $this->assertStringContainsString((string) $input, $message, 'Expected input value in explanation');
        $this->assertStringContainsString('greater than or equal to 10', $message, 'Expected threshold in explanation');
    }

    /**
     * @return iterable<string, array{0: int|float}>
     */
    public static function provideValidNumbers(): iterable
    {
        yield 'equal' => [10];
        yield 'above' => [11];
        yield 'large int' => [100];
        yield 'float above' => [10.01];
        yield 'float equal' => [10.0];
    }

    /**
     * @return iterable<string, array{0: int|float}>
     */
    public static function provideInvalidNumbers(): iterable
    {
        yield 'just below' => [9.999];
        yield 'int below' => [9];
        yield 'zero' => [0];
        yield 'negative' => [-10];
    }
}
