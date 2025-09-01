<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property\Exception;

use Hermiod\Resource\Property\Exception\Exception;
use Hermiod\Resource\Property\Exception\InvalidPropertyNameException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidPropertyNameException::class)]
final class InvalidPropertyNameExceptionTest extends TestCase
{
    #[DataProvider('invalidPropertyNameProvider')]
    public function testNewWithInvalidPropertyNames(string $name): void
    {
        $exception = InvalidPropertyNameException::new($name);

        $this->assertInstanceOf(InvalidPropertyNameException::class, $exception);
        $this->assertInstanceOf(\DomainException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $expectedMessage = \sprintf(
            "The property name '%s' is not valid. Must be a valid PHP property name.",
            $name
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionHierarchy(): void
    {
        $exception = InvalidPropertyNameException::new('123invalid');

        $this->assertInstanceOf(\DomainException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testMessageFormat(): void
    {
        $name = 'invalid-name';
        $exception = InvalidPropertyNameException::new($name);

        $message = $exception->getMessage();

        $this->assertStringStartsWith("The property name '$name'", $message);
        $this->assertStringContainsString('is not valid', $message);
        $this->assertStringContainsString('Must be a valid PHP property name', $message);
        $this->assertStringEndsWith('.', $message);
    }

    public function testMessageContainsProvidedName(): void
    {
        $name = 'test-property';
        $exception = InvalidPropertyNameException::new($name);

        $this->assertStringContainsString("'$name'", $exception->getMessage());
    }

    public function testWithEmptyString(): void
    {
        $exception = InvalidPropertyNameException::new('');

        $this->assertStringContainsString("''", $exception->getMessage());
    }

    public function testWithSpecialCharacters(): void
    {
        $name = "property'with\"quotes\nand\ttabs";
        $exception = InvalidPropertyNameException::new($name);

        $this->assertStringContainsString($name, $exception->getMessage());
    }

    public static function invalidPropertyNameProvider(): array
    {
        return [
            // Names starting with numbers
            'starts with number' => ['123property'],
            'only numbers' => ['123'],
            'number prefix' => ['1name'],

            // Names with spaces
            'contains space' => ['property name'],
            'leading space' => [' property'],
            'trailing space' => ['property '],
            'only spaces' => ['   '],

            // Names with special characters
            'contains dash' => ['property-name'],
            'contains dot' => ['property.name'],
            'contains comma' => ['property,name'],
            'contains semicolon' => ['property;name'],
            'contains colon' => ['property:name'],
            'contains at sign' => ['property@name'],
            'contains hash' => ['property#name'],
            'contains dollar' => ['property$name'],
            'contains percent' => ['property%name'],
            'contains ampersand' => ['property&name'],
            'contains asterisk' => ['property*name'],
            'contains plus' => ['property+name'],
            'contains equals' => ['property=name'],
            'contains brackets' => ['property[name]'],
            'contains braces' => ['property{name}'],
            'contains parentheses' => ['property(name)'],
            'contains pipe' => ['property|name'],
            'contains backslash' => ['property\\name'],
            'contains forward slash' => ['property/name'],
            'contains question mark' => ['property?name'],
            'contains exclamation' => ['property!name'],
            'contains less than' => ['property<name'],
            'contains greater than' => ['property>name'],
            'contains quotes' => ['property"name'],
            'contains single quote' => ["property'name"],
            'contains backtick' => ['property`name'],
            'contains tilde' => ['property~name'],

            // Names with control characters
            'contains newline' => ["property\nname"],
            'contains tab' => ["property\tname"],
            'contains carriage return' => ["property\rname"],
            'contains null byte' => ["property\0name"],
            'contains vertical tab' => ["property\vname"],
            'contains form feed' => ["property\fname"],

            // Unicode and non-ASCII characters
            'contains accented chars' => ['propertyñame'],
            'contains unicode' => ['property™name'],
            'contains emoji' => ['property😀name'],
            'contains cyrillic' => ['propertyПname'],
            'contains chinese' => ['property中name'],
            'contains arabic' => ['propertyالname'],

            // PHP reserved words (if used as property names)
            'php keyword class' => ['class'],
            'php keyword function' => ['function'],
            'php keyword return' => ['return'],
            'php keyword if' => ['if'],
            'php keyword else' => ['else'],
            'php keyword foreach' => ['foreach'],
            'php keyword while' => ['while'],
            'php keyword for' => ['for'],
            'php keyword switch' => ['switch'],
            'php keyword case' => ['case'],
            'php keyword break' => ['break'],
            'php keyword continue' => ['continue'],
            'php keyword try' => ['try'],
            'php keyword catch' => ['catch'],
            'php keyword finally' => ['finally'],
            'php keyword throw' => ['throw'],
            'php keyword new' => ['new'],
            'php keyword clone' => ['clone'],
            'php keyword instanceof' => ['instanceof'],

            // Edge cases
            'empty string' => [''],
            'only underscore' => ['_'],
            'double underscore' => ['__'],
            'underscore with number' => ['_123'],
            'very long name' => [\str_repeat('a', 1000)],
            'mixed invalid chars' => ['property-name.with@special#chars'],

            // Starting with invalid characters
            'starts with dash' => ['-property'],
            'starts with dot' => ['.property'],
            'starts with space' => [' property'],
            'starts with special char' => ['@property'],

            // Complex invalid patterns
            'all special chars' => ['!@#$%^&*()'],
            'mixed numbers and special' => ['123-abc@def'],
            'path-like' => ['path/to/property'],
            'url-like' => ['http://property'],
            'email-like' => ['property@domain.com'],
        ];
    }
}
