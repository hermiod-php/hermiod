<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ArrayValueStringIsUuid;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayValueStringIsUuid::class)]
final class ArrayValueStringIsUuidTest extends TestCase
{
    #[DataProvider('provideValidUuids')]
    public function testAcceptsValidUuidStrings(string $uuid): void
    {
        $constraint = new ArrayValueStringIsUuid();

        $this->assertTrue(
            $constraint->mapValueMatchesConstraint($uuid),
            'Expected valid UUID string to be accepted'
        );
    }

    #[DataProvider('provideInvalidUuids')]
    public function testRejectsInvalidUuidValues(mixed $value): void
    {
        $constraint = new ArrayValueStringIsUuid();

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected invalid UUID value to be rejected'
        );
    }

    public function testReturnsExplanationForInvalidUuid(): void
    {
        $constraint = new ArrayValueStringIsUuid();
        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.id');

        $message = $constraint->getMismatchExplanation($path, 'not-a-uuid');

        $this->assertSame(
            "$.id must be a UUID string but 'not-a-uuid' given",
            $message,
            'Expected mismatch explanation to match'
        );
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function provideValidUuids(): array
    {
        return [
            'uppercase' => ['123E4567-E89B-12D3-A456-426614174000'],
            'lowercase' => ['123e4567-e89b-12d3-a456-426614174000'],
            'mixed case' => ['123E4567-e89B-12D3-a456-426614174000'],
            'zeroes' => ['00000000-0000-0000-0000-000000000000'],
            'FFFFFs' => ['ffffffff-ffff-ffff-ffff-ffffffffffff'],
        ];
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function provideInvalidUuids(): \Generator
    {
        $valid = '00000000-0000-0000-0000-000000000000';

        yield 'missing dash' => ['123e4567e89b12d3a456426614174000'];
        yield 'too short' => ['123e4567-e89b-12d3-a456-42661417'];
        yield 'not a string' => [123];
        yield 'null' => [null];
        yield 'empty string' => [''];

        for ($i = 0, $length = \strlen($valid); $i < $length; $i++) {
            if ($valid[$i] === '-') {
                continue;
            }

            $mutated = \substr_replace($valid, 'X', $i, 1);

            yield $mutated => [$mutated];
        }
    }
}
