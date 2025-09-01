<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property\Exception;

use Hermiod\Resource\Property\Exception\Exception;
use Hermiod\Resource\Property\Exception\InvalidUuidValueException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidUuidValueException::class)]
final class InvalidUuidValueExceptionTest extends TestCase
{
    #[DataProvider('invalidUuidProvider')]
    public function testNewWithInvalidUuidValues(string $uuid): void
    {
        $exception = InvalidUuidValueException::new($uuid);

        $this->assertInstanceOf(InvalidUuidValueException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $expectedMessage = \sprintf("Unable to parse UUID value from '%s'", $uuid);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertNull($exception->getPrevious());
    }

    #[DataProvider('invalidUuidProvider')]
    public function testNewWithPreviousException(string $uuid): void
    {
        $previousException = new \Exception('Original parsing error');
        $exception = InvalidUuidValueException::new($uuid, $previousException);

        $this->assertInstanceOf(InvalidUuidValueException::class, $exception);
        $this->assertSame($previousException, $exception->getPrevious());

        $expectedMessage = \sprintf("Unable to parse UUID value from '%s'", $uuid);

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionHierarchy(): void
    {
        $exception = InvalidUuidValueException::new('invalid-uuid');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(\LogicException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testMessageFormat(): void
    {
        $uuid = 'invalid-uuid-value';
        $exception = InvalidUuidValueException::new($uuid);

        $message = $exception->getMessage();

        $this->assertStringStartsWith('Unable to parse UUID value from', $message);
        $this->assertStringContainsString("'$uuid'", $message);
    }

    public function testWithEmptyString(): void
    {
        $exception = InvalidUuidValueException::new('');

        $this->assertStringContainsString("''", $exception->getMessage());
    }

    public function testWithSpecialCharacters(): void
    {
        $uuid = "uuid'with\"quotes\nand\ttabs";
        $exception = InvalidUuidValueException::new($uuid);

        $this->assertStringContainsString($uuid, $exception->getMessage());
    }

    public function testChainedExceptions(): void
    {
        $rootCause = new \RuntimeException('Root cause');
        $intermediateCause = new \Exception('Intermediate cause', 0, $rootCause);
        $exception = InvalidUuidValueException::new('bad-uuid', $intermediateCause);

        $this->assertSame($intermediateCause, $exception->getPrevious());
        $this->assertSame($rootCause, $exception->getPrevious()?->getPrevious());
    }

    public function testMessageDoesNotContainPreviousExceptionMessage(): void
    {
        $previousMessage = 'Specific parsing error details';
        $previous = new \Exception($previousMessage);

        $exception = InvalidUuidValueException::new('bad-uuid', $previous);

        $message = $exception->getMessage();

        $this->assertStringNotContainsString($previousMessage, $message);
        $this->assertStringContainsString("'bad-uuid'", $message);
    }

    public static function invalidUuidProvider(): array
    {
        return [
            // Completely invalid formats
            'random string' => ['not-a-uuid'],
            'empty string' => [''],
            'numeric string' => ['123456'],
            'simple text' => ['hello world'],

            // Wrong UUID formats
            'no dashes' => ['550e8400e29b41d4a716446655440000'],
            'wrong dash positions' => ['550e8400-e29b-41d4a716-446655440000'],
            'extra dashes' => ['550e8400--e29b-41d4-a716-446655440000'],
            'missing segments' => ['550e8400-e29b-41d4-a716'],
            'extra segments' => ['550e8400-e29b-41d4-a716-446655440000-extra'],

            // Wrong length segments
            'short first segment' => ['550e840-e29b-41d4-a716-446655440000'],
            'long first segment' => ['550e84000-e29b-41d4-a716-446655440000'],
            'short second segment' => ['550e8400-e29-41d4-a716-446655440000'],
            'long second segment' => ['550e8400-e29bb-41d4-a716-446655440000'],
            'short third segment' => ['550e8400-e29b-41d-a716-446655440000'],
            'long third segment' => ['550e8400-e29b-41d44-a716-446655440000'],
            'short fourth segment' => ['550e8400-e29b-41d4-a71-446655440000'],
            'long fourth segment' => ['550e8400-e29b-41d4-a7166-446655440000'],
            'short fifth segment' => ['550e8400-e29b-41d4-a716-44665544000'],
            'long fifth segment' => ['550e8400-e29b-41d4-a716-4466554400000'],

            // Invalid characters
            'contains g' => ['550e8400-e29b-41d4-a716-44665544000g'],
            'contains z' => ['550e8400-e29b-41d4-a716-44665544000z'],
            'contains space' => ['550e8400-e29b-41d4-a716-446655440 00'],
            'contains special chars' => ['550e8400-e29b-41d4-a716-446655440@00'],
            'contains unicode' => ['550e8400-e29b-41d4-a716-446655440ñ00'],

            // Partial UUIDs
            'too short overall' => ['550e8400-e29b-41d4-a716-44665544'],
            'too long overall' => ['550e8400-e29b-41d4-a716-4466554400001'],
            'missing last character' => ['550e8400-e29b-41d4-a716-44665544000'],
            'extra character' => ['550e8400-e29b-41d4-a716-4466554400001'],

            // Case variations that might be invalid
            'mixed with invalid chars' => ['550E8400-E29B-41D4-A716-44665544000G'],

            // Whitespace issues
            'leading space' => [' 550e8400-e29b-41d4-a716-446655440000'],
            'trailing space' => ['550e8400-e29b-41d4-a716-446655440000 '],
            'internal space' => ['550e8400-e29b-41d4-a716- 46655440000'],
            'tab character' => ['550e8400-e29b-41d4-a716-446655440000\t'],
            'newline character' => ['550e8400-e29b-41d4-a716-446655440000\n'],

            // Other formats that might be confused with UUIDs
            'base64' => ['VVDkAOKbQdSnFkRmVUQAAA=='],
            'hex without dashes' => ['deadbeefcafebabe1234567890abcdef'],
            'with braces' => ['{550e8400-e29b-41d4-a716-446655440000}'],
            'with brackets' => ['[550e8400-e29b-41d4-a716-446655440000]'],
            'with parentheses' => ['(550e8400-e29b-41d4-a716-446655440000)'],

            // Security/injection attempts
            'sql injection attempt' => ["550e8400-e29b-41d4-a716-446655440000'; DROP TABLE users; --"],
            'script injection' => ['550e8400-e29b-41d4-a716-446655440000<script>alert(1)</script>'],

            // Version specific issues (invalid version indicators)
            'invalid version' => ['550e8400-e29b-91d4-a716-446655440000'], // version 9 doesn't exist
            'malformed version' => ['550e8400-e29b-x1d4-a716-446655440000'], // 'x' instead of version digit

            // Edge cases
            'only dashes' => ['----'],
            'only hex chars' => ['abcdef'],
            'numbers only' => ['12345'],
            'mixed valid format wrong content' => ['gggggggg-gggg-gggg-gggg-gggggggggggg'],
        ];
    }
}

