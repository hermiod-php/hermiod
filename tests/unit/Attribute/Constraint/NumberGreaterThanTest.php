<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\NumberGreaterThan;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NumberGreaterThan::class)]
final class NumberGreaterThanTest extends TestCase
{
    #[DataProvider('provideValidNumbers')]
    public function testValidNumbersPassConstraint(int|float $input): void
    {
        $constraint = new NumberGreaterThan(10);

        $this->assertTrue(
            $constraint->valueMatchesConstraint($input),
            "Expected {$input} to be greater than 10"
        );
    }

    #[DataProvider('provideInvalidNumbers')]
    public function testInvalidNumbersFailConstraint(int|float $input): void
    {
        $constraint = new NumberGreaterThan(10);

        $this->assertFalse(
            $constraint->valueMatchesConstraint($input),
            "Expected {$input} to not be greater than 10"
        );
    }

    #[DataProvider('provideInvalidNumbers')]
    public function testMismatchExplanationIncludesPathAndValues(int|float $input): void
    {
        $constraint = new NumberGreaterThan(10);

        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.value');

        $message = $constraint->getMismatchExplanation($path, $input);

        $this->assertStringContainsString('$.value', $message, 'Expected path in explanation');
        $this->assertStringContainsString((string) $input, $message, 'Expected input value in explanation');
        $this->assertStringContainsString('greater than 10', $message, 'Expected threshold in explanation');
    }

    /**
     * @return iterable<string, array{0: int|float}>
     */
    public static function provideValidNumbers(): iterable
    {
        yield 'just above' => [11];
        yield 'well above' => [100];
        yield 'float above' => [10.1];
        yield 'large float' => [99.999];
    }

    /**
     * @return iterable<string, array{0: int|float}>
     */
    public static function provideInvalidNumbers(): iterable
    {
        yield 'equal' => [10];
        yield 'just below' => [9];
        yield 'float below' => [9.99];
        yield 'zero' => [0];
        yield 'negative' => [-5];
    }
}
