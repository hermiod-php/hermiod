<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ArrayValueStringMatchesRegex;
use Hermiod\Attribute\Constraint\Exception\InvalidRegexException;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayValueStringMatchesRegex::class)]
final class ArrayValueStringMatchesRegexTest extends TestCase
{
    #[DataProvider('provideValidValues')]
    public function testValidValuesMatchRegex(string $value): void
    {
        $constraint = new ArrayValueStringMatchesRegex('/^[a-z]{3}\d{3}$/');

        $result = $constraint->mapValueMatchesConstraint($value);

        $this->assertTrue($result, "Expected '{$value}' to match the regex");
    }

    #[DataProvider('provideInvalidValues')]
    public function testInvalidValuesDoNotMatchRegex(mixed $value): void
    {
        $constraint = new ArrayValueStringMatchesRegex('/^[a-z]{3}\d{3}$/');

        $result = $constraint->mapValueMatchesConstraint($value);

        $this->assertFalse($result, 'Expected value to not match regex: ' . \var_export($value, true));
    }

    #[DataProvider('provideInvalidValues')]
    public function testMismatchExplanationIncludesValue(mixed $value): void
    {
        $constraint = new ArrayValueStringMatchesRegex('/^[a-z]{3}\d{3}$/');

        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.foo');

        $message = $constraint->getMismatchExplanation($path, $value);

        $this->assertStringContainsString((string) $value, $message, 'Expected explanation to contain the invalid value');
        $this->assertStringContainsString('$.foo', $message, 'Expected explanation to contain the path');
    }

    public function testInvalidRegexThrowsValueError(): void
    {
        $this->expectException(InvalidRegexException::class);
        $this->expectExceptionMessage("The regex '/[unterminated' is invalid due to: No ending delimiter '/' found");

        new ArrayValueStringMatchesRegex('/[unterminated');
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'lowercase and digits' => ['abc123'];
        yield 'zxy999' => ['zxy999'];
        yield 'mno000' => ['mno000'];
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideInvalidValues(): iterable
    {
        yield 'empty string' => [''];
        yield 'too short' => ['abc12'];
        yield 'uppercase' => ['ABC123'];
        yield 'extra digits' => ['abc1234'];
        yield 'prefix' => ['xabc123'];
    }
}
