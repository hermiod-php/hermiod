<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ObjectValueNumberLessThanOrEqual;
use Hermiod\Attribute\Constraint\Traits\MapValueNumberLessThanOrEqual;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectValueNumberLessThanOrEqual::class)]
#[CoversClass(MapValueNumberLessThanOrEqual::class)]
final class ObjectValueNumberLessThanOrEqualTest extends TestCase
{
    #[DataProvider('provideValidValues')]
    public function testValidValuesAreAccepted(mixed $value): void
    {
        $constraint = new ObjectValueNumberLessThanOrEqual(100);

        $this->assertTrue(
            $constraint->mapValueMatchesConstraint($value),
            'Expected numeric value less than or equal to 100 to be accepted'
        );
    }

    #[DataProvider('provideInvalidNumericValues')]
    public function testInvalidNumericValuesAreRejected(mixed $value): void
    {
        $constraint = new ObjectValueNumberLessThanOrEqual(100);

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected numeric value greater than 100 to be rejected'
        );
    }

    #[DataProvider('provideInvalidTypeValues')]
    public function testInvalidTypesAreRejected(mixed $value): void
    {
        $constraint = new ObjectValueNumberLessThanOrEqual(100);

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected non-numeric value to be rejected'
        );
    }

    public function testMismatchExplanationIncludesDetails(): void
    {
        $constraint = new ObjectValueNumberLessThanOrEqual(100);

        $path = $this->createMock(PathInterface::class);
        $path->method('__toString')->willReturn('$.value');

        $message = $constraint->getMismatchExplanation($path, 101);

        $this->assertStringContainsString('$.value', $message);
        $this->assertStringContainsString('101', $message);
        $this->assertStringContainsString('100', $message);
        $this->assertStringContainsString('less than or equal to', $message);

        $message = $constraint->getMismatchExplanation($path, 101.1);

        $this->assertStringContainsString('101.1', $message);
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'less int' => [99];
        yield 'equal int' => [100];
        yield 'less float' => [99.999];
        yield 'equal float' => [100.0];
        yield 'zero' => [0];
        yield 'negative' => [-5];
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideInvalidNumericValues(): iterable
    {
        yield 'greater int' => [101];
        yield 'greater float small' => [100.0001];
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

