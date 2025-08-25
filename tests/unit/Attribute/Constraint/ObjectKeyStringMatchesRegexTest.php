<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Exception\InvalidRegexException;
use Hermiod\Attribute\Constraint\ObjectKeyStringMatchesRegex;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectKeyStringMatchesRegex::class)]
final class ObjectKeyStringMatchesRegexTest extends TestCase
{
    #[DataProvider('provideMatchingKeys')]
    public function testMatchingKeysAreAccepted(string $regex, string $key): void
    {
        $constraint = new ObjectKeyStringMatchesRegex($regex);

        $this->assertTrue(
            $constraint->mapKeyMatchesConstraint($key),
            "Expected key '{$key}' to match regex '{$regex}'"
        );
    }

    #[DataProvider('provideNonMatchingKeys')]
    public function testNonMatchingKeysAreRejected(string $regex, string $key): void
    {
        $constraint = new ObjectKeyStringMatchesRegex($regex);

        $this->assertFalse(
            $constraint->mapKeyMatchesConstraint($key),
            "Expected key '{$key}' to be rejected by regex '{$regex}'"
        );
    }

    #[DataProvider('provideNonMatchingKeys')]
    public function testMismatchExplanationIncludesDetails(string $regex, string $key): void
    {
        $constraint = new ObjectKeyStringMatchesRegex($regex);

        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.keys');

        $message = $constraint->getMismatchExplanation($path, $key);

        $this->assertStringContainsString('$.keys', $message, 'Expected explanation to include the path');
        $this->assertStringContainsString($regex, $message, 'Expected explanation to include the regex');
        $this->assertStringContainsString($key, $message, 'Expected explanation to include the key');
    }

    public function testInvalidRegexThrowsValueError(): void
    {
        $this->expectException(InvalidRegexException::class);
        $this->expectExceptionMessage("The regex '/[unterminated' is invalid due to: No ending delimiter '/' found");

        new ObjectKeyStringMatchesRegex('/[unterminated');
    }

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function provideMatchingKeys(): iterable
    {
        yield 'letters only' => ['/^[a-z]+$/', 'abc'];
        yield 'letters and numbers' => ['/^[a-z0-9]+$/', 'abc123'];
        yield 'email-ish key' => ['/^.+@.+\..+$/', 'foo@bar.com'];
    }

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function provideNonMatchingKeys(): iterable
    {
        yield 'uppercase rejected' => ['/^[a-z]+$/', 'ABC'];
        yield 'symbols rejected' => ['/^[a-z0-9]+$/', 'abc-123'];
        yield 'missing @ in email' => ['/^.+@.+\..+$/', 'foobar.com'];
    }
}
