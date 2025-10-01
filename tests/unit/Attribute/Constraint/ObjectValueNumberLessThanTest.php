<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ObjectValueNumberLessThan;
use Hermiod\Attribute\Constraint\Traits\MapValueNumberLessThan;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectValueNumberLessThan::class)]
#[CoversClass(MapValueNumberLessThan::class)]
final class ObjectValueNumberLessThanTest extends TestCase
{
    #[DataProvider('provideValidValues')]
    public function testValidValuesAreAccepted(mixed $value): void
    {
        $constraint = new ObjectValueNumberLessThan(100);

        $this->assertTrue(
            $constraint->mapValueMatchesConstraint($value),
            'Expected numeric value less than 100 to be accepted'
        );
    }

    #[DataProvider('provideInvalidNumericValues')]
    public function testInvalidNumericValuesAreRejected(mixed $value): void
    {
        $constraint = new ObjectValueNumberLessThan(100);

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected numeric value not less than 100 to be rejected'
        );
    }

    #[DataProvider('provideInvalidTypeValues')]
    public function testInvalidTypesAreRejected(mixed $value): void
    {
        $constraint = new ObjectValueNumberLessThan(100);

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected non-numeric value to be rejected'
        );
    }

    #[DataProvider('provideInvalidNumericValues')]
    public function testMismatchExplanationIncludesDetails(mixed $value): void
    {
        $constraint = new ObjectValueNumberLessThan(100);

        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.value');

        $message = $constraint->getMismatchExplanation($path, $value);

        $this->assertStringContainsString('$.value', $message, 'Expected explanation to include the path');
        $this->assertStringContainsString((string) $value, $message, 'Expected explanation to include the actual value');
        $this->assertStringContainsString('less than 100', $message, 'Expected explanation to mention the comparison');
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'zero' => [0];
        yield 'positive int' => [99];
        yield 'positive float' => [99.999];
        yield 'negative' => [-1];
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideInvalidNumericValues(): iterable
    {
        yield 'equal' => [100];
        yield 'greater int' => [101];
        yield 'greater float' => [100.0001];
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideInvalidTypeValues(): iterable
    {
        yield 'string numeric' => ['100'];
        yield 'bool true' => [true];
        yield 'array' => [[100]];
        yield 'object' => [new \stdClass()];
        yield 'null' => [null];
    }
}

