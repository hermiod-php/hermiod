<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint\Exception;

use Hermiod\Attribute\Constraint\Exception\Exception;
use Hermiod\Attribute\Constraint\Exception\InvalidRegexException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InvalidRegexExceptionTest extends TestCase
{
    public function testInvalidRegexCreatesExceptionWithCorrectMessage(): void
    {
        $regex = '/[a-z+/';
        $error = 'missing closing bracket';

        $exception = InvalidRegexException::invalidRegex($regex, $error);

        $this->assertInstanceOf(InvalidRegexException::class, $exception);
        $this->assertSame("The regex '/[a-z+/' is invalid due to: missing closing bracket", $exception->getMessage());
    }

    public function testInvalidRegexWithEmptyErrorUsesUnknown(): void
    {
        $regex = '/invalid[/';
        $error = '';

        $exception = InvalidRegexException::invalidRegex($regex, $error);

        $this->assertSame("The regex '/invalid[/' is invalid due to: unknown", $exception->getMessage());
    }

    public function testInvalidRegexWithWhitespaceOnlyErrorUsesUnknown(): void
    {
        $regex = '/test/';
        $error = '   ';

        $exception = InvalidRegexException::invalidRegex($regex, $error);

        $this->assertSame("The regex '/test/' is invalid due to:    ", $exception->getMessage());
    }

    public function testInvalidRegexWithSpecialCharacters(): void
    {
        $regex = '/[\\w+]*$/';
        $error = 'backslash error';

        $exception = InvalidRegexException::invalidRegex($regex, $error);

        $this->assertSame("The regex '/[\\w+]*$/' is invalid due to: backslash error", $exception->getMessage());
    }

    public function testInvalidRegexWithLongErrorMessage(): void
    {
        $regex = '/test/';
        $error = \str_repeat('very long error message ', 10);

        $exception = InvalidRegexException::invalidRegex($regex, $error);

        $expectedMessage = "The regex '/test/' is invalid due to: " . $error;
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testInvalidRegexWithEmptyRegex(): void
    {
        $regex = '';
        $error = 'empty regex';

        $exception = InvalidRegexException::invalidRegex($regex, $error);

        $this->assertSame("The regex '' is invalid due to: empty regex", $exception->getMessage());
    }

    public function testExceptionExtendsInvalidArgumentException(): void
    {
        $exception = InvalidRegexException::invalidRegex('/test/', 'error');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }

    public function testExceptionImplementsExceptionInterface(): void
    {
        $exception = InvalidRegexException::invalidRegex('/test/', 'error');

        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testExceptionIsFinal(): void
    {
        $reflection = new \ReflectionClass(InvalidRegexException::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testExceptionCodeAndPreviousAreDefaults(): void
    {
        $exception = InvalidRegexException::invalidRegex('/test/', 'error');

        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    #[DataProvider('regexErrorProvider')]
    public function testVariousRegexAndErrorCombinations(string $regex, string $error, string $expectedMessage): void
    {
        $exception = InvalidRegexException::invalidRegex($regex, $error);

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public static function regexErrorProvider(): array
    {
        return [
            'simple regex with error' => [
                '/[a-z]/',
                'simple error',
                "The regex '/[a-z]/' is invalid due to: simple error"
            ],
            'complex regex with detailed error' => [
                '/(?P<name>[a-zA-Z]+)\s+(?P<age>\d+)/',
                'named capture groups not supported',
                "The regex '/(?P<name>[a-zA-Z]+)\s+(?P<age>\d+)/' is invalid due to: named capture groups not supported"
            ],
            'regex with quotes' => [
                '/test"quotes\'/',
                'quote handling error',
                "The regex '/test\"quotes'/' is invalid due to: quote handling error"
            ],
            'unicode regex' => [
                '/\p{L}+/u',
                'unicode not supported',
                "The regex '/\p{L}+/u' is invalid due to: unicode not supported"
            ],
            'empty error message' => [
                '/valid/',
                '',
                "The regex '/valid/' is invalid due to: unknown"
            ]
        ];
    }
}
