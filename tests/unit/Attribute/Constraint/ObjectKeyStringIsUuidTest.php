<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ObjectKeyStringIsUuid;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectKeyStringIsUuid::class)]
final class ObjectKeyStringIsUuidTest extends TestCase
{
    #[DataProvider('provideValidUuids')]
    public function testValidUuidKeysAreAccepted(string $uuid): void
    {
        $constraint = new ObjectKeyStringIsUuid();

        $this->assertTrue(
            $constraint->mapKeyMatchesConstraint($uuid),
            "Expected '{$uuid}' to be recognised as a valid UUID"
        );
    }

    #[DataProvider('provideInvalidUuids')]
    public function testInvalidUuidKeysAreRejected(mixed $value): void
    {
        // Only strings can be passed to mapKeyMatchesConstraint
        if (!\is_string($value)) {
            $this->expectException(\TypeError::class);
            (new ObjectKeyStringIsUuid())->mapKeyMatchesConstraint($value);
            return;
        }

        $constraint = new ObjectKeyStringIsUuid();

        $this->assertFalse(
            $constraint->mapKeyMatchesConstraint($value),
            "Expected '{$value}' to be rejected as an invalid UUID"
        );
    }

    #[DataProvider('provideInvalidUuids')]
    public function testMismatchExplanationIncludesDetails(mixed $value): void
    {
        if (!\is_string($value)) {
            $this->expectException(\TypeError::class);
            (new ObjectKeyStringIsUuid())->getMismatchExplanation(
                $this->createMock(PathInterface::class),
                $value
            );
            return;
        }

        $constraint = new ObjectKeyStringIsUuid();

        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.ids');

        $message = $constraint->getMismatchExplanation($path, $value);

        $this->assertStringContainsString('$.ids', $message, 'Expected message to include the path');
        $this->assertStringContainsString($value, $message, 'Expected message to include the invalid key');
        $this->assertStringContainsString('UUID', $message, 'Expected message to mention UUID validity');
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function provideValidUuids(): iterable
    {
        yield 'v4 lowercase' => ['123e4567-e89b-12d3-a456-426614174000'];
        yield 'v4 uppercase' => ['550E8400-E29B-41D4-A716-446655440000'];
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideInvalidUuids(): iterable
    {
        $valid = '123e4567-e89b-12d3-a456-426614174000';

        yield 'missing dash' => ['123e4567e89b12d3a456426614174000'];
        yield 'too short' => ['123e4567-e89b-12d3-a456-42661417'];
        yield 'not a string' => [123];
        yield 'null' => [null];
        yield 'empty string' => [''];
        yield 'invalid characters' => ['zzze4567-e89b-12d3-a456-426614174000'];

        // Mutate each character to 'z'
        for ($i = 0; $i < \strlen($valid); $i++) {
            if ($valid[$i] === '-') {
                continue;
            }

            $mutated = \substr_replace($valid, 'z', $i, 1);
            yield "mutated position {$i}" => [$mutated];
        }
    }
}
