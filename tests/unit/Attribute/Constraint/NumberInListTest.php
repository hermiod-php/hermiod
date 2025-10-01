<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\NumberInList;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NumberInList::class)]
final class NumberInListTest extends TestCase
{
    #[DataProvider('provideValidNumbers')]
    public function testValidValuesMatchConstraint(int|float $input): void
    {
        $constraint = new NumberInList(10, 20, 30.5);

        $this->assertTrue(
            $constraint->valueMatchesConstraint($input),
            "Expected {$input} to be in the list"
        );
    }

    #[DataProvider('provideInvalidNumbers')]
    public function testInvalidValuesDoNotMatchConstraint(int|float $input): void
    {
        $constraint = new NumberInList(10, 20, 30.5);

        $this->assertFalse(
            $constraint->valueMatchesConstraint($input),
            "Expected {$input} to not be in the list"
        );
    }

    public function testIntAndFloatValuesAreComparable(): void
    {
        $constraint = new NumberInList(10);

        $this->assertTrue(
            $constraint->valueMatchesConstraint(10.0),
            "Expected int 10 to match float 10.0"
        );
    }

    public function testFloatAndIntValuesAreComparable(): void
    {
        $constraint = new NumberInList(10.0);

        $this->assertTrue(
            $constraint->valueMatchesConstraint(10),
            "Expected float 10.0 to match int 10"
        );
    }

    #[DataProvider('provideInvalidNumbers')]
    public function testMismatchExplanationIncludesDetails(int|float $input): void
    {
        $constraint = new NumberInList(10, 20, 30.5);

        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.number');

        $message = $constraint->getMismatchExplanation($path, $input);

        $this->assertStringContainsString('$.number', $message, 'Expected path in explanation');
        $this->assertStringContainsString((string) $input, $message, 'Expected input value in explanation');
        $this->assertStringContainsString('10', $message, 'Expected list to contain 10');
        $this->assertStringContainsString('30.5', $message, 'Expected list to contain 30.5');
    }

    /**
     * @return iterable<string, array{0: int|float}>
     */
    public static function provideValidNumbers(): iterable
    {
        yield 'first' => [10];
        yield 'middle' => [20];
        yield 'last float' => [30.5];
    }

    /**
     * @return iterable<string, array{0: int|float}>
     */
    public static function provideInvalidNumbers(): iterable
    {
        yield 'missing int' => [15];
        yield 'nearby float' => [30.0];
        yield 'negative' => [-10];
        yield 'zero' => [0];
    }
}
