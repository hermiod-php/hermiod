<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ArrayValueStringIsEmail;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayValueStringIsEmail::class)]
final class ArrayValueStringIsEmailTest extends TestCase
{
    #[DataProvider('provideValidEmails')]
    public function testAcceptsValidEmailAddresses(string $email): void
    {
        $constraint = new ArrayValueStringIsEmail();

        $this->assertTrue(
            $constraint->mapValueMatchesConstraint($email),
            'Expected valid email to be accepted'
        );
    }

    #[DataProvider('provideInvalidEmails')]
    public function testRejectsInvalidEmailAddresses(mixed $value): void
    {
        $constraint = new ArrayValueStringIsEmail();

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected invalid email to be rejected'
        );
    }

    public function testReturnsExplanationForInvalidEmail(): void
    {
        $constraint = new ArrayValueStringIsEmail();
        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.email');

        $message = $constraint->getMismatchExplanation($path, 'invalid-email');

        $this->assertSame(
            "$.email must be an email address string but 'invalid-email' given",
            $message,
            'Expected mismatch explanation to match'
        );
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function provideValidEmails(): array
    {
        return [
            'simple' => ['user@example.com'],
            'with dot' => ['first.last@example.co.uk'],
            'with plus' => ['user+tag@example.org'],
        ];
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function provideInvalidEmails(): array
    {
        return [
            'missing at' => ['user.example.com'],
            'missing domain' => ['user@'],
            'missing user' => ['@example.com'],
            'empty string' => [''],
            'null' => [null],
            'int' => [123],
        ];
    }
}
