<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\StringConstraintInterface;
use Hermiod\Attribute\Constraint\StringIsUuid;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringIsUuid::class)]
final class StringIsUuidTest extends TestCase
{
    #[DataProvider('validUuidProvider')]
    public function testValidUuids(string $uuid): void
    {
        $constraint = new StringIsUuid();

        $this->assertTrue($constraint->valueMatchesConstraint($uuid));
    }

    #[DataProvider('invalidUuidProvider')]
    public function testInvalidUuids(string $uuid): void
    {
        $constraint = new StringIsUuid();

        $this->assertFalse($constraint->valueMatchesConstraint($uuid));
    }

    #[DataProvider('mismatchExplanationProvider')]
    public function testGetMismatchExplanation(string $pathString, string $value, string $expectedMessage): void
    {
        $constraint = new StringIsUuid();
        $path = $this->createMock(PathInterface::class);
        $path->method('__toString')->willReturn($pathString);

        $explanation = $constraint->getMismatchExplanation($path, $value);

        $this->assertSame($expectedMessage, $explanation);
    }

    public function testImplementsStringConstraintInterface(): void
    {
        $constraint = new StringIsUuid();

        $this->assertInstanceOf(StringConstraintInterface::class, $constraint);
    }

    public function testIsAttribute(): void
    {
        $reflection = new \ReflectionClass(StringIsUuid::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $this->assertSame(\Attribute::TARGET_PROPERTY, $attributes[0]->getArguments()[0]);
    }

    public static function validUuidProvider(): array
    {
        return [
            // UUID v1 (time-based)
            'uuid v1' => ['6ba7b810-9dad-11d1-80b4-00c04fd430c8'],
            'uuid v1 lowercase' => ['550e8400-e29b-41d4-a716-446655440000'],

            // UUID v3 (MD5 hash)
            'uuid v3' => ['6fa459ea-ee8a-3ca4-894e-db77e160355e'],

            // UUID v4 (random)
            'uuid v4' => ['6ba7b812-9dad-41d1-80b4-00c04fd430c8'],
            'uuid v4 another' => ['f47ac10b-58cc-4372-a567-0e02b2c3d479'],
            'uuid v4 mixed case' => ['F47AC10B-58CC-4372-A567-0E02B2C3D479'],

            // UUID v5 (SHA-1 hash)
            'uuid v5' => ['6ba7b811-9dad-51d1-80b4-00c04fd430c8'],

            // Edge cases with valid format
            'all zeros' => ['00000000-0000-0000-0000-000000000000'],
            'all f lowercase' => ['ffffffff-ffff-ffff-ffff-ffffffffffff'],
            'all F uppercase' => ['FFFFFFFF-FFFF-FFFF-FFFF-FFFFFFFFFFFF'],
            'mixed case valid' => ['6BA7b810-9DaD-11d1-80B4-00c04FD430c8'],
        ];
    }

    public static function invalidUuidProvider(): array
    {
        return [
            // Wrong length
            'too short' => ['6ba7b810-9dad-11d1-80b4-00c04fd430c'],
            'too long' => ['6ba7b810-9dad-11d1-80b4-00c04fd430c88'],
            'missing segment' => ['6ba7b810-9dad-11d1-80b4'],
            'extra segment' => ['6ba7b810-9dad-11d1-80b4-00c04fd430c8-extra'],

            // Wrong format
            'no dashes' => ['6ba7b8109dad11d180b400c04fd430c8'],
            'wrong dash positions' => ['6ba7b8-109dad-11d1-80b400c04fd430c8'],
            'extra dashes' => ['6ba7b810--9dad-11d1-80b4-00c04fd430c8'],
            'dashes at wrong positions' => ['6ba7b81-09dad-11d1-80b4-00c04fd430c8'],

            // Invalid characters
            'contains g' => ['6ba7b810-9dad-11d1-80b4-00c04fd430g8'],
            'contains z' => ['6ba7b810-9dad-11d1-80b4-00c04fd430z8'],
            'contains space' => ['6ba7b810-9dad-11d1-80b4-00c04fd430 8'],
            'contains special chars' => ['6ba7b810-9dad-11d1-80b4-00c04fd430!8'],
            'contains unicode' => ['6ba7b810-9dad-11d1-80b4-00c04fd430ñ8'],

            // Empty and edge cases
            'empty string' => [''],
            'only dashes' => ['--------'],
            'only numbers' => ['1234567890123456789012345678901234567890'],
            'valid length wrong format' => ['6ba7b810x9dadx11d1x80b4x00c04fd430c8'],

            // Case sensitivity edge cases
            'mixed with invalid chars' => ['6BA7B810-9DAD-11D1-80B4-00C04FD430G8'],

            // Whitespace issues
            'leading space' => [' 6ba7b810-9dad-11d1-80b4-00c04fd430c8'],
            'trailing space' => ['6ba7b810-9dad-11d1-80b4-00c04fd430c8 '],
            'internal space' => ['6ba7b810-9dad-11d1-80b4- 0c04fd430c8'],
            'tab character' => ['6ba7b810-9dad-11d1-80b4-00c04fd430c8\t'],
            'newline character' => ['6ba7b810-9dad-11d1-80b4-00c04fd430c8\n'],

            // Security/injection attempts
            'sql injection attempt' => ["6ba7b810-9dad-11d1-80b4-00c04fd430'; DROP TABLE users; --"],
            'script injection' => ['6ba7b810-9dad-11d1-80b4-00c04fd430<script>alert(1)</script>'],

            // Malformed segments
            'segment too short' => ['6ba7b81-9dad-11d1-80b4-00c04fd430c8'],
            'segment too long' => ['6ba7b8100-9dad-11d1-80b4-00c04fd430c8'],
            'all segments wrong length' => ['6ba7b-9da-11d-80b-00c04fd430'],
        ];
    }

    public static function mismatchExplanationProvider(): array
    {
        return [
            'simple path' => [
                'field',
                'invalid-uuid',
                "field must be a UUID string but 'invalid-uuid' given"
            ],
            'nested path' => [
                'user.profile.id',
                'not-a-uuid',
                "user.profile.id must be a UUID string but 'not-a-uuid' given"
            ],
            'array path' => [
                'items[0].uuid',
                '123',
                "items[0].uuid must be a UUID string but '123' given"
            ],
            'special characters in value' => [
                'id',
                'invalid"uuid\'with\nspecial\tchars',
                "id must be a UUID string but 'invalid\"uuid'with\\nspecial\\tchars' given"
            ],
            'empty value' => [
                'identifier',
                '',
                "identifier must be a UUID string but '' given"
            ],
            'long invalid value' => [
                'uuid_field',
                \str_repeat('a', 100),
                "uuid_field must be a UUID string but '" . \str_repeat('a', 100) . "' given"
            ],
        ];
    }
}
