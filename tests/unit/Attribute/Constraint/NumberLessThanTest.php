<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\NumberLessThan;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NumberLessThan::class)]
final class NumberLessThanTest extends TestCase
{
    #[DataProvider('provideValidNumbers')]
    public function testValidValuesMatchConstraint(int|float $input): void
    {
        $constraint = new NumberLessThan(100);

        $this->assertTrue(
            $constraint->valueMatchesConstraint($input),
            "Expected {$input} to be less than 100"
        );
    }

    #[DataProvider('provideInvalidNumbers')]
    public function testInvalidValuesDoNotMatchConstraint(int|float $input): void
    {
        $constraint = new NumberLessThan(100);

        $this->assertFalse(
            $constraint->valueMatchesConstraint($input),
            "Expected {$input} to not be less than 100"
        );
    }

    #[DataProvider('provideInvalidNumbers')]
    public function testMismatchExplanationIncludesDetails(int|float $input): void
    {
        $constraint = new NumberLessThan(100);

        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.value');

        $message = $constraint->getMismatchExplanation($path, $input);

        $this->assertStringContainsString('$.value', $message, 'Expected explanation to include the path');
        $this->assertStringContainsString((string) $input, $message, 'Expected explanation to include the actual value');
        $this->assertStringContainsString('less than 100', $message, 'Expected explanation to mention the comparison');
    }

    /**
     * @return iterable<string, array{0: int|float}>
     */
    public static function provideValidNumbers(): iterable
    {
        yield 'zero' => [0];
        yield 'positive int' => [99];
        yield 'positive float' => [99.999];
        yield 'negative' => [-100];
    }

    /**
     * @return iterable<string, array{0: int|float}>
     */
    public static function provideInvalidNumbers(): iterable
    {
        yield 'equal' => [100];
        yield 'greater int' => [101];
        yield 'greater float' => [100.0001];
    }
}
