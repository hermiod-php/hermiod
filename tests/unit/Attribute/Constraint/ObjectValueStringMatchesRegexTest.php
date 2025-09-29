<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\Exception\InvalidRegexException;
use Hermiod\Attribute\Constraint\ObjectValueConstraintInterface;
use Hermiod\Attribute\Constraint\ObjectValueStringMatchesRegex;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Traits\JsonCompatibleTypeName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectValueStringMatchesRegex::class)]
final class ObjectValueStringMatchesRegexTest extends TestCase
{
    use JsonCompatibleTypeName;

    #[DataProvider('validRegexMatchProvider')]
    public function testValidRegexWithMatchingValues(string $regex, string $value): void
    {
        $constraint = new ObjectValueStringMatchesRegex($regex);

        $this->assertTrue($constraint->mapValueMatchesConstraint($value));
    }

    #[DataProvider('validRegexNonMatchProvider')]
    public function testValidRegexWithNonMatchingValues(string $regex, string $value): void
    {
        $constraint = new ObjectValueStringMatchesRegex($regex);

        $this->assertFalse($constraint->mapValueMatchesConstraint($value));
    }

    #[DataProvider('invalidRegexProvider')]
    public function testInvalidRegexThrowsException(string $invalidRegex): void
    {
        $this->expectException(InvalidRegexException::class);

        new ObjectValueStringMatchesRegex($invalidRegex);
    }

    #[DataProvider('mismatchExplanationProvider')]
    public function testGetMismatchExplanation(string $regex, string $pathString, string $value, string $expectedMessage): void
    {
        $constraint = new ObjectValueStringMatchesRegex($regex);
        $path = $this->createMock(PathInterface::class);
        $path->method('__toString')->willReturn($pathString);

        $explanation = $constraint->getMismatchExplanation($path, $value);

        $this->assertSame($expectedMessage, $explanation);
    }

    #[DataProvider('invalidTypeProvider')]
    public function testInvalidTypesAreRejected(mixed $value): void
    {
        $constraint = new ObjectValueStringMatchesRegex('/test/');

        $this->assertFalse($constraint->mapValueMatchesConstraint($value));
    }

    #[DataProvider('invalidTypeProvider')]
    public function testInvalidTypeMismatchExplanation(mixed $value): void
    {
        $constraint = new ObjectValueStringMatchesRegex('/test/');
        $path = $this->createMock(PathInterface::class);
        $path->method('__toString')->willReturn('field');

        $message = $constraint->getMismatchExplanation($path, $value);

        $this->assertSame("field must be a string matching the regex '/test/' but " . $this->getTypeName($value) . ' given', $message);
    }

    public function testImplementsObjectValueConstraintInterface(): void
    {
        $constraint = new ObjectValueStringMatchesRegex('/test/');

        $this->assertInstanceOf(ObjectValueConstraintInterface::class, $constraint);
    }

    public function testIsAttribute(): void
    {
        $reflection = new \ReflectionClass(ObjectValueStringMatchesRegex::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $this->assertSame(\Attribute::TARGET_PROPERTY, $attributes[0]->getArguments()[0]);
    }

    public static function validRegexMatchProvider(): array
    {
        return [
            'simple word match' => ['/hello/', 'hello'],
            'case sensitive match' => ['/Hello/', 'Hello'],
            'partial match' => ['/test/', 'testing'],
            'start anchor match' => ['/^start/', 'start of string'],
            'end anchor match' => ['/end$/', 'string end'],
            'full string match' => ['/^complete$/', 'complete'],
            'digit match' => ['/\d+/', '12345'],
            'word character match' => ['/\w+/', 'word_123'],
            'whitespace match' => ['/\s+/', '   '],
            'custom character class' => ['/[abc]+/', 'abcabc'],
            'negated character class' => ['/[^xyz]+/', 'hello'],
            'zero or more' => ['/a*/', ''],
            'zero or more with content' => ['/a*/', 'aaaa'],
            'one or more' => ['/a+/', 'aaa'],
            'optional' => ['/colou?r/', 'color'],
            'optional with u' => ['/colou?r/', 'colour'],
            'exact count' => ['/a{3}/', 'aaa'],
            'range count' => ['/a{2,4}/', 'aaa'],
            'minimum count' => ['/a{2,}/', 'aaaaa'],
            'dot wildcard' => ['/.+/', 'anything'],
            'escaped dot' => ['/\./', '.'],
            'pipe alternation cat' => ['/cat|dog/', 'cat'],
            'pipe alternation dog' => ['/cat|dog/', 'dog'],
            'simple group' => ['/(test)/', 'test'],
            'non-capturing group' => ['/(?:test)/', 'test'],
            'multiple groups' => ['/(hello) (world)/', 'hello world'],
            'case insensitive' => ['/HELLO/i', 'hello'],
            'multiline' => ['/^test/m', "line1\ntest"],
            'single line mode' => ['/test.end/s', "test\nend"],
            'extended regex' => ['/t e s t/x', 'test'],
            'unicode flag' => ['/café/u', 'café'],
            'unicode property' => ['/\p{L}+/u', 'héllo'],
            'unicode category' => ['/\p{Nd}+/u', '１２３'],
            'email pattern' => ['/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'test@example.com'],
            'phone pattern' => ['/^\d{3}-\d{3}-\d{4}$/', '123-456-7890'],
            'hex color' => ['/^#[0-9a-fA-F]{6}$/', '#FF5733'],
            'ip address' => ['/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', '192.168.1.1'],
            'empty string with star' => ['/.*/', ''],
            'newline in string' => ['/line\nbreak/', "line\nbreak"],
            'tab character' => ['/tab\there/', "tab\there"],
            'special chars class' => ['/[\\[\\](){}.*+?^$|\\\\]/', '['],
        ];
    }

    public static function validRegexNonMatchProvider(): array
    {
        return [
            'simple word no match' => ['/hello/', 'goodbye'],
            'case sensitive no match' => ['/Hello/', 'hello'],
            'start anchor no match' => ['/^start/', 'not start'],
            'end anchor no match' => ['/end$/', 'end not'],
            'full string no match' => ['/^complete$/', 'incomplete'],
            'digit no match' => ['/^\d+$/', 'abc'],
            'word character no match' => ['/^\w+$/', '!!!'],
            'whitespace no match' => ['/^\s+$/', 'text'],
            'custom character class no match' => ['/^[abc]+$/', 'xyz'],
            'negated character class no match' => ['/^[^xyz]+$/', 'xyz'],
            'one or more no match' => ['/^a+$/', ''],
            'exact count no match' => ['/^a{3}$/', 'aa'],
            'range count no match' => ['/^a{2,4}$/', 'a'],
            'minimum count no match' => ['/^a{2,}$/', 'a'],
            'email pattern no match' => ['/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', 'invalid-email'],
            'phone pattern no match' => ['/^\d{3}-\d{3}-\d{4}$/', '123-45-6789'],
            'hex color no match' => ['/^#[0-9a-fA-F]{6}$/', '#GG5733'],
            'ip address no match' => ['/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', '300.168.1.1'],
            'case sensitive with wrong case' => ['/HELLO/', 'hello'],
            'required content no match' => ['/^.+$/', ''],
            'specific pattern no match' => ['/^test$/', ''],
        ];
    }

    public static function invalidRegexProvider(): array
    {
        return [
            'no delimiters' => ['hello'],
            'mismatched delimiters' => ['/hello#'],
            'unclosed group' => ['/test(/'],
            'unclosed bracket' => ['/test[/'],
            'invalid flag' => ['/test/z'],
            'only delimiter' => ['/'],
            'invalid repetition' => ['/a{5,2}/'],
            'invalid backreference' => ['/\9/'],
            'invalid unicode' => ['/\p{Invalid}/u'],
            'nested groups issue' => ['/(((/'],
            'invalid lookahead' => ['/(?=/'],
            'invalid character class' => ['/[z-a]/'],
        ];
    }

    public static function mismatchExplanationProvider(): array
    {
        return [
            'simple pattern' => [
                '/^test$/',
                'field',
                'invalid',
                "field must be a string matching the regex '/^test$/' but 'invalid' given"
            ],
            'nested path' => [
                '/\\d+/',
                'user.age',
                'abc',
                "user.age must be a string matching the regex '/\\d+/' but 'abc' given"
            ],
            'array path' => [
                '/^[A-Z]+$/',
                'items[0].code',
                'lowercase',
                "items[0].code must be a string matching the regex '/^[A-Z]+$/' but 'lowercase' given"
            ],
            'complex regex' => [
                '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$/',
                'email',
                'invalid-email',
                "email must be a string matching the regex '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$/' but 'invalid-email' given"
            ],
            'special characters in value' => [
                '/^simple$/',
                'field',
                'value"with\'quotes\nand\ttabs',
                "field must be a string matching the regex '/^simple$/' but 'value\"with'quotes\\nand\\ttabs' given"
            ],
            'empty value' => [
                '/^.+$/',
                'required_field',
                '',
                "required_field must be a string matching the regex '/^.+$/' but '' given"
            ],
            'regex with quotes' => [
                '/test"pattern/',
                'field',
                'nomatch',
                "field must be a string matching the regex '/test\"pattern/' but 'nomatch' given"
            ],
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
            'array' => [['a']],
            'object' => [new \stdClass()],
        ];
    }
}

