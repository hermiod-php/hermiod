<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property\Exception;

use Hermiod\Resource\Property\Exception\Exception;
use Hermiod\Resource\Property\Exception\InvalidDateTimeValueException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidDateTimeValueException::class)]
final class InvalidDateTimeValueExceptionTest extends TestCase
{
    #[DataProvider('invalidDateTimeProvider')]
    public function testNewWithInvalidDateTimeValues(string $datetime): void
    {
        $exception = InvalidDateTimeValueException::new($datetime);

        $this->assertInstanceOf(InvalidDateTimeValueException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $expectedMessage = \sprintf("Unable to parse ISO 8601 datetime value from '%s'.", $datetime);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertNull($exception->getPrevious());
    }

    #[DataProvider('invalidDateTimeProvider')]
    public function testNewWithPreviousException(string $datetime): void
    {
        $previousException = new \Exception('Original parsing error');
        $exception = InvalidDateTimeValueException::new($datetime, $previousException);

        $this->assertInstanceOf(InvalidDateTimeValueException::class, $exception);
        $this->assertSame($previousException, $exception->getPrevious());

        $expectedMessage = \sprintf(
            "Unable to parse ISO 8601 datetime value from '%s'. %s",
            $datetime,
            $previousException->getMessage()
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionHierarchy(): void
    {
        $exception = InvalidDateTimeValueException::new('invalid-date');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(\LogicException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testMessageFormat(): void
    {
        $datetime = 'invalid-datetime';
        $exception = InvalidDateTimeValueException::new($datetime);

        $message = $exception->getMessage();

        $this->assertStringStartsWith('Unable to parse ISO 8601 datetime value from', $message);
        $this->assertStringContainsString("'$datetime'", $message);
        $this->assertStringEndsWith('.', $message);
    }

    public function testMessageWithPreviousExceptionFormat(): void
    {
        $datetime = 'bad-date';
        $previousMessage = 'Specific parsing error details';
        $previous = new \Exception($previousMessage);

        $exception = InvalidDateTimeValueException::new($datetime, $previous);

        $message = $exception->getMessage();

        $this->assertStringContainsString("'$datetime'", $message);
        $this->assertStringContainsString($previousMessage, $message);
        $this->assertStringContainsString('. ', $message); // Space after period before previous message
    }

    public function testWithEmptyString(): void
    {
        $exception = InvalidDateTimeValueException::new('');

        $this->assertStringContainsString("''", $exception->getMessage());
    }

    public function testWithSpecialCharacters(): void
    {
        $datetime = "date'with\"quotes\nand\ttabs";
        $exception = InvalidDateTimeValueException::new($datetime);

        $this->assertStringContainsString($datetime, $exception->getMessage());
    }

    public function testChainedExceptions(): void
    {
        $rootCause = new \RuntimeException('Root cause');
        $intermediateCause = new \Exception('Intermediate cause', 0, $rootCause);
        $exception = InvalidDateTimeValueException::new('bad-date', $intermediateCause);

        $this->assertSame($intermediateCause, $exception->getPrevious());
        $this->assertSame($rootCause, $exception->getPrevious()?->getPrevious());
    }

    public static function invalidDateTimeProvider(): array
    {
        return [
            // Completely invalid formats
            'random string' => ['not-a-date'],
            'empty string' => [''],
            'numeric string' => ['123456'],
            'partial date' => ['2023-01'],
            'partial time' => ['12:30'],

            // Wrong date formats
            'us format' => ['01/15/2023'],
            'european format' => ['15/01/2023'],
            'dots format' => ['2023.01.15'],
            'space separated' => ['2023 01 15'],
            'no separators' => ['20230115'],

            // Wrong time formats
            'time only' => ['14:30:45'],
            'am/pm format' => ['2:30 PM'],
            '12 hour format' => ['2023-01-15 2:30 PM'],

            // Invalid ISO 8601 variations
            'wrong separator' => ['2023/01/15T14:30:45Z'],
            'missing T separator' => ['2023-01-15 14:30:45Z'],
            'wrong timezone format' => ['2023-01-15T14:30:45GMT'],
            'invalid timezone' => ['2023-01-15T14:30:45+25:00'],

            // Invalid dates
            'invalid month' => ['2023-13-15T14:30:45Z'],
            'invalid day' => ['2023-02-30T14:30:45Z'],
            'invalid hour' => ['2023-01-15T25:30:45Z'],
            'invalid minute' => ['2023-01-15T14:60:45Z'],
            'invalid second' => ['2023-01-15T14:30:60Z'],

            // Edge cases
            'leading zeros wrong' => ['23-1-5T4:3:5Z'],
            'extra characters' => ['2023-01-15T14:30:45Z extra'],
            'missing components' => ['2023-01-15T14:30Z'],
            'malformed microseconds' => ['2023-01-15T14:30:45.Z'],

            // Special characters
            'with quotes' => ["2023-01-15'T'14:30:45Z"],
            'with newlines' => ["2023-01-15\nT14:30:45Z"],
            'with tabs' => ["2023-01-15\tT14:30:45Z"],
            'unicode characters' => ['2023年01月15日'],

            // Other formats that might be confused with ISO 8601
            'rfc 2822' => ['Mon, 15 Jan 2023 14:30:45 +0000'],
            'unix timestamp' => ['1673794245'],
            'excel format' => ['2023-01-15 14:30:45.000'],
            'sql format' => ['2023-01-15 14:30:45.123456'],
        ];
    }
}

