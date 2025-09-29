<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ObjectValueConstraintInterface;
use Hermiod\Attribute\Constraint\ObjectValueStringIsUuid;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectValueStringIsUuid::class)]
final class ObjectValueStringIsUuidTest extends TestCase
{
    #[DataProvider('validUuidProvider')]
    public function testValidUuids(string $uuid): void
    {
        $constraint = new ObjectValueStringIsUuid();

        $this->assertTrue($constraint->mapValueMatchesConstraint($uuid));
    }

    #[DataProvider('invalidUuidProvider')]
    public function testInvalidUuids(string $uuid): void
    {
        $constraint = new ObjectValueStringIsUuid();

        $this->assertFalse($constraint->mapValueMatchesConstraint($uuid));
    }

    #[DataProvider('invalidTypeProvider')]
    public function testInvalidTypes(mixed $value): void
    {
        $constraint = new ObjectValueStringIsUuid();

        $this->assertFalse($constraint->mapValueMatchesConstraint($value));
    }

    #[DataProvider('mismatchExplanationProvider')]
    public function testGetMismatchExplanation(string $pathString, mixed $value, string $expected): void
    {
        $constraint = new ObjectValueStringIsUuid();
        $path = $this->createMock(PathInterface::class);
        $path->method('__toString')->willReturn($pathString);

        $message = $constraint->getMismatchExplanation($path, $value);

        $this->assertSame($expected, $message);
    }

    public function testImplementsObjectValueConstraintInterface(): void
    {
        $constraint = new ObjectValueStringIsUuid();

        $this->assertInstanceOf(ObjectValueConstraintInterface::class, $constraint);
    }

    public function testIsAttribute(): void
    {
        $reflection = new \ReflectionClass(ObjectValueStringIsUuid::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $this->assertSame(\Attribute::TARGET_PROPERTY, $attributes[0]->getArguments()[0]);
    }

    public static function validUuidProvider(): array
    {
        return [
            'uuid v1' => ['6ba7b810-9dad-11d1-80b4-00c04fd430c8'],
            'uuid v4' => ['f47ac10b-58cc-4372-a567-0e02b2c3d479'],
            'uuid v3' => ['6fa459ea-ee8a-3ca4-894e-db77e160355e'],
            'uuid v5' => ['6ba7b811-9dad-51d1-80b4-00c04fd430c8'],
            'all zeros' => ['00000000-0000-0000-0000-000000000000'],
            'all f lowercase' => ['ffffffff-ffff-ffff-ffff-ffffffffffff'],
            'all F uppercase' => ['FFFFFFFF-FFFF-FFFF-FFFF-FFFFFFFFFFFF'],
            'mixed case valid' => ['6BA7b810-9DaD-11d1-80B4-00c04FD430c8'],
        ];
    }

    public static function invalidUuidProvider(): array
    {
        return [
            'too short' => ['6ba7b810-9dad-11d1-80b4-00c04fd430c'],
            'too long' => ['6ba7b810-9dad-11d1-80b4-00c04fd430c88'],
            'missing segment' => ['6ba7b810-9dad-11d1-80b4'],
            'extra segment' => ['6ba7b810-9dad-11d1-80b4-00c04fd430c8-extra'],
            'no dashes' => ['6ba7b8109dad11d180b400c04fd430c8'],
            'wrong dash positions' => ['6ba7b8-109dad-11d1-80b400c04fd430c8'],
            'extra dashes' => ['6ba7b810--9dad-11d1-80b4-00c04fd430c8'],
            'dashes wrong positions' => ['6ba7b81-09dad-11d1-80b4-00c04fd430c8'],
            'contains g' => ['6ba7b810-9dad-11d1-80b4-00c04fd430g8'],
            'contains z' => ['6ba7b810-9dad-11d1-80b4-00c04fd430z8'],
            'contains space' => ['6ba7b810-9dad-11d1-80b4-00c04fd430 8'],
            'contains special char' => ['6ba7b810-9dad-11d1-80b4-00c04fd430!8'],
            'contains unicode' => ['6ba7b810-9dad-11d1-80b4-00c04fd430ñ8'],
            'empty string' => [''],
            'only dashes' => ['--------'],
            'only numbers' => ['1234567890123456789012345678901234567890'],
            'valid length wrong format' => ['6ba7b810x9dadx11d1x80b4x00c04fd430c8'],
            'mixed invalid chars' => ['6BA7B810-9DAD-11D1-80B4-00C04FD430G8'],
            'leading space' => [' 6ba7b810-9dad-11d1-80b4-00c04fd430c8'],
            'trailing space' => ['6ba7b810-9dad-11d1-80b4-00c04fd430c8 '],
            'internal space' => ['6ba7b810-9dad-11d1-80b4- 0c04fd430c8'],
            'tab char' => ["6ba7b810-9dad-11d1-80b4-00c04fd430c8\t"],
            'newline char' => ["6ba7b810-9dad-11d1-80b4-00c04fd430c8\n"],
            'sql injection attempt' => ["6ba7b810-9dad-11d1-80b4-00c04fd430'; DROP TABLE users; --"],
            'script injection' => ['6ba7b810-9dad-11d1-80b4-00c04fd430<script>alert(1)</script>'],
            'segment too short' => ['6ba7b81-9dad-11d1-80b4-00c04fd430c8'],
            'segment too long' => ['6ba7b8100-9dad-11d1-80b4-00c04fd430c8'],
            'all segments wrong length' => ['6ba7b-9da-11d-80b-00c04fd430'],
        ];
    }

    public static function invalidTypeProvider(): array
    {
        return [
            'int' => [42],
            'float' => [3.14],
            'bool true' => [true],
            'bool false' => [false],
            'null' => [null],
            'array' => [['uuid']],
            'object' => [new \stdClass()],
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
            'special characters' => [
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
            'invalid type int' => [
                'uuid',
                42,
                'uuid must be a UUID string but int given'
            ],
            'invalid type array' => [
                'uuid',
                ['uuid'],
                'uuid must be a UUID string but array given'
            ],
            'invalid type object' => [
                'uuid',
                new \stdClass(),
                'uuid must be a UUID string but object given'
            ],
        ];
    }
}
