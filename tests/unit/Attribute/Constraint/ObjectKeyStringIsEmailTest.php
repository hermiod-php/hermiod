<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ObjectKeyStringIsEmail;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectKeyStringIsEmail::class)]
final class ObjectKeyStringIsEmailTest extends TestCase
{
    #[DataProvider('provideValidEmails')]
    public function testValidEmailKeysAreAccepted(string $email): void
    {
        $constraint = new ObjectKeyStringIsEmail();

        $this->assertTrue(
            $constraint->mapKeyMatchesConstraint($email),
            "Expected '{$email}' to be recognised as a valid email"
        );
    }

    #[DataProvider('provideInvalidEmails')]
    public function testInvalidEmailKeysAreRejected(string $email): void
    {
        $constraint = new ObjectKeyStringIsEmail();

        $this->assertFalse(
            $constraint->mapKeyMatchesConstraint($email),
            "Expected '{$email}' to be rejected as an invalid email"
        );
    }

    #[DataProvider('provideInvalidEmails')]
    public function testMismatchExplanationIncludesDetails(string $email): void
    {
        $constraint = new ObjectKeyStringIsEmail();

        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.emails');

        $message = $constraint->getMismatchExplanation($path, $email);

        $this->assertStringContainsString('$.emails', $message, 'Expected message to include the path');
        $this->assertStringContainsString($email, $message, 'Expected message to include the invalid key');
        $this->assertStringContainsString('valid email', $message, 'Expected message to mention email validity');
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function provideValidEmails(): iterable
    {
        yield 'simple' => ['user@example.com'];
        yield 'dot in local' => ['john.doe@example.org'];
        yield 'plus alias' => ['admin+tag@domain.co.uk'];
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function provideInvalidEmails(): iterable
    {
        yield 'missing at' => ['userexample.com'];
        yield 'double at' => ['user@@example.com'];
        yield 'no domain' => ['user@'];
        yield 'empty' => [''];
        yield 'space in email' => ['foo bar@example.com'];
    }
}
