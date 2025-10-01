<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ObjectValueNumberGreaterThan;
use Hermiod\Attribute\Constraint\Traits\MapValueNumberGreaterThan;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectValueNumberGreaterThan::class)]
#[CoversClass(MapValueNumberGreaterThan::class)]
final class ObjectValueNumberGreaterThanTest extends TestCase
{
    #[DataProvider('provideValidValues')]
    public function testValidValuesAreAccepted(mixed $value): void
    {
        $constraint = new ObjectValueNumberGreaterThan(5);

        $this->assertTrue(
            $constraint->mapValueMatchesConstraint($value),
            'Expected numeric value greater than 5 to be accepted'
        );
    }

    #[DataProvider('provideInvalidNumericValues')]
    public function testInvalidNumericValuesAreRejected(mixed $value): void
    {
        $constraint = new ObjectValueNumberGreaterThan(5);

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected numeric value not greater than 5 to be rejected'
        );
    }

    #[DataProvider('provideInvalidTypeValues')]
    public function testInvalidTypesAreRejected(mixed $value): void
    {
        $constraint = new ObjectValueNumberGreaterThan(5);

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected non-numeric value to be rejected'
        );
    }

    #[DataProvider('provideInvalidNumericValues')]
    public function testMismatchExplanationIncludesDetails(mixed $value): void
    {
        $constraint = new ObjectValueNumberGreaterThan(5);

        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.value');

        $message = $constraint->getMismatchExplanation($path, $value);

        $this->assertStringContainsString('$.value', $message, 'Expected explanation to include the path');
        $this->assertStringContainsString((string) $value, $message, 'Expected explanation to include the actual value');
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'greater int' => [10];
        yield 'greater float' => [5.1];
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideInvalidNumericValues(): iterable
    {
        yield 'equal int' => [5];
        yield 'less int' => [3];
        yield 'less float' => [4.9];
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideInvalidTypeValues(): iterable
    {
        yield 'string numeric' => ['6'];
        yield 'bool true' => [true];
        yield 'array' => [[5]];
        yield 'object' => [new \stdClass()];
        yield 'null' => [null];
    }
}
