<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\StringConstraintInterface;
use Hermiod\Attribute\Constraint\StringIsEmail;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StringIsEmailTest extends TestCase
{
    #[DataProvider('validEmailProvider')]
    public function testValidEmails(string $email): void
    {
        $constraint = new StringIsEmail();

        $this->assertTrue($constraint->valueMatchesConstraint($email));
    }

    #[DataProvider('invalidEmailProvider')]
    public function testInvalidEmails(string $email): void
    {
        $constraint = new StringIsEmail();

        $this->assertFalse($constraint->valueMatchesConstraint($email));
    }

    #[DataProvider('mismatchExplanationProvider')]
    public function testMismatchExplanationVariousScenarios(string $pathString, string $invalidValue, string $expected): void
    {
        $constraint = new StringIsEmail();
        $path = $this->createMock(PathInterface::class);
        $path->method('__toString')->willReturn($pathString);

        $explanation = $constraint->getMismatchExplanation($path, $invalidValue);

        $this->assertSame($expected, $explanation);
    }

    public function testImplementsStringConstraintInterface(): void
    {
        $constraint = new StringIsEmail();

        $this->assertInstanceOf(StringConstraintInterface::class, $constraint);
    }

    public function testClassIsFinal(): void
    {
        $reflection = new \ReflectionClass(StringIsEmail::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testClassHasAttributeAttribute(): void
    {
        $reflection = new \ReflectionClass(StringIsEmail::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $this->assertSame(\Attribute::TARGET_PROPERTY, $attributes[0]->getArguments()[0]);
    }

    public static function validEmailProvider(): array
    {
        return [
            'simple email' => ['test@example.com'],
            'email with subdomain' => ['user@mail.example.com'],
            'email with plus' => ['user+tag@example.com'],
            'email with dot in local part' => ['first.last@example.com'],
            'email with numbers' => ['user123@example123.com'],
            'email with dashes' => ['user-name@example-domain.com'],
            'email with underscore' => ['user_name@example.com'],
            'short domain' => ['test@ex.co'],
            'long tld' => ['test@example.information'],
            'email with digits in domain' => ['test@123domain.com'],
            'complex email' => ['user.name+tag@example.co.uk'],
            'subdomain with dashes' => ['user-name@sub-domain.example.com'],
            // UTF-8 and internationalised domain names
            'utf8 local part' => ['tëst@example.com'],
            'unicode local part' => ['用户@example.com'],
            'emoji in local part' => ['test🚀@example.com'],
            'accented characters' => ['café@example.com'],
            'german umlaut' => ['müller@example.com'],
            'japanese characters' => ['テスト@example.com'],
            'arabic characters' => ['اختبار@example.com'],
            // Additional valid formats
            'quoted local part' => ['"test user"@example.com'],
            'short quoted local part' => ['"t"@example.com'],
            'quoted with spaces' => ['"john doe"@example.com'],
            'very long local part' => [\str_repeat('a', 64) . '@example.com'],
            'ip address domain' => ['test@[192.168.1.1]'],
            'ipv6 domain' => ['test@[2001:db8::1]'],
            // Additional coverage for IP literals
            'ipv4 literal domain' => ['test@[192.168.1.100]'],
        ];
    }

    public static function invalidEmailProvider(): array
    {
        return [
            'empty string' => [''],
            'no at sign' => ['testexample.com'],
            'multiple at signs' => ['test@@example.com'],
            'missing local part' => ['@example.com'],
            'missing domain' => ['test@'],
            'missing tld' => ['test@example'],
            'spaces in email' => ['test @example.com'],
            'invalid characters' => ['test<>@example.com'],
            'double dots' => ['test..name@example.com'],
            'starting with dot' => ['.test@example.com'],
            'ending with dot' => ['test.@example.com'],
            'invalid domain' => ['test@.example.com'],
            'just text' => ['not an email'],
            'only at sign' => ['@'],
            'double at at end' => ['test@@'],
            'simple invalid' => ['invalid-email'],
            // Security and edge case vulnerabilities
            'xss attempt' => ['<script>alert("xss")</script>@example.com'],
            'sql injection attempt' => ["'; DROP TABLE users; --@example.com"],
            'null byte injection' => ["test\0@example.com"],
            'newline injection' => ["test\n@example.com"],
            'carriage return injection' => ["test\r@example.com"],
            'tab injection' => ["test\t@example.com"],
            'path traversal attempt' => ['../../../etc/passwd@example.com'],
            'command injection' => ['test; rm -rf /@example.com'],
            'ldap injection' => ['test)(uid=*)@example.com'],
            'header injection' => ["test\r\nBcc: attacker@evil.com@example.com"],
            // Length and format edge cases
            'too long local part' => [\str_repeat('a', 65) . '@example.com'],
            'too long domain' => ['test@' . \str_repeat('a', 254) . '.com'],
            'too long overall' => [\str_repeat('a', 320) . '@example.com'],
            'consecutive dots in domain' => ['test@example..com'],
            'domain starting with dot' => ['test@.example.com'],
            'domain ending with dot' => ['test@example.com.'],
            'domain with only dots' => ['test@...'],
            'unescaped quotes' => ['test"quote@example.com'],
            'unmatched quotes' => ['"test@example.com'],
            'backslash without escape' => ['test\\@example.com'],
            // Unicode edge cases
            'cyrillic characters' => ['тест@example.com'], // Mixed Cyrillic local + Latin domain
            'mixed scripts vulnerability' => ['аdmin@example.com'], // Cyrillic 'а' looks like Latin 'a'
            'homograph attack' => ['раypal@example.com'], // Cyrillic characters resembling PayPal
            'zero width characters' => ['test​@example.com'], // Contains zero-width space
            'rtl override character' => ['test‮@example.com'],
            'bidi override attack' => ['user‮moc.elpmaxe@tset'],
            // Protocol and scheme attempts
            'javascript protocol' => ['javascript:alert(1)@example.com'],
            'data uri attempt' => ['data:text/html,<script>alert(1)</script>@example.com'],
            'file protocol' => ['file:///etc/passwd@example.com'],
            'http protocol' => ['http://evil.com@example.com'],
            // Format confusion
            'multiple @ in quoted' => ['"test@test"@example@com'],
            'ip with invalid format' => ['test@[999.999.999.999]'],
            'invalid ipv6' => ['test@[gggg::1]'],
            'malformed brackets' => ['test@[example.com'],
            'nested brackets' => ['test@[[example.com]]'],
            // Additional coverage for IP literal validation
            'invalid ipv4 literal' => ['test@[999.999.999.999]'], // Invalid IPv4 in brackets
            'malformed ipv4 literal' => ['test@[192.168.1]'], // Incomplete IPv4
            'non-ip literal' => ['test@[not-an-ip]'], // Not an IP at all
            // Additional coverage for domain label validation
            'empty domain label' => ['test@example..com'], // This should trigger empty label check
            'domain starting with double dot' => ['test@..example.com'], // Creates empty first label
            'domain ending with double dot' => ['test@example.com..'], // Creates empty last label
            'domain label too long' => ['test@' . \str_repeat('a', 64) . '.com'], // 64 char label (max is 63)
            'domain label starting with dash' => ['test@-example.com'],
            'domain label ending with dash' => ['test@example-.com'],
            'multiple invalid label issues' => ['test@-' . \str_repeat('b', 64) . '-.com'],
            // Encoding attacks
            'url encoded' => ['test%40example.com'],
            'html entities' => ['test&#64;example.com'],
            'double encoding' => ['test%2540example.com'],
            // Whitespace variations
            'leading whitespace' => [' test@example.com'],
            'trailing whitespace' => ['test@example.com '],
            'non-breaking space' => ['test @example.com'], // Contains non-breaking space
            'various unicode spaces' => ['test @example.com'], // Em space
        ];
    }

    public static function mismatchExplanationProvider(): array
    {
        return [
            'simple field' => [
                'email',
                'invalid',
                "email must be an email address but 'invalid' given"
            ],
            'nested field' => [
                'user.contact.email',
                'not-an-email',
                "user.contact.email must be an email address but 'not-an-email' given"
            ],
            'array index' => [
                'contacts[0].email',
                'bad@',
                "contacts[0].email must be an email address but 'bad@' given"
            ],
            'complex path' => [
                'data.users[5].contact_info.primary_email',
                'malformed@@email',
                "data.users[5].contact_info.primary_email must be an email address but 'malformed@@email' given"
            ],
            'special characters in value' => [
                'field',
                'invalid"with\'quotes',
                "field must be an email address but 'invalid\"with'quotes' given"
            ],
            'empty string value' => [
                'email_field',
                '',
                "email_field must be an email address but '' given"
            ],
            'xss attempt in explanation' => [
                'field',
                '<script>alert("xss")</script>@example.com',
                "field must be an email address but '<script>alert(\"xss\")</script>@example.com' given"
            ],
            'sql injection in explanation' => [
                'field',
                "'; DROP TABLE users; --@example.com",
                "field must be an email address but ''; DROP TABLE users; --@example.com' given"
            ],
            'unicode characters in explanation' => [
                'field',
                'тест@example.com',
                "field must be an email address but 'тест@example.com' given"
            ],
            'null byte in explanation' => [
                'field',
                "test\0@example.com",
                "field must be an email address but 'test\0@example.com' given"
            ],
        ];
    }
}
