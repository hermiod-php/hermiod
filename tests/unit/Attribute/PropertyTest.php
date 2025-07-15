<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute;

use Hermiod\Attribute\Property;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Property::class)]
final class PropertyTest extends TestCase
{
    public function testDefaults(): void
    {
        $property = new Property();

        $this->assertNull(
            $property->getNameOverride(),
            'Expected null when no name is passed.'
        );
    }

    #[DataProvider('validNames')]
    public function testNameIsTrimmed(string $input, string $expected): void
    {
        $property = new Property($input);

        $this->assertSame(
            $expected,
            $property->getNameOverride(),
            'Expected trimmed property name.'
        );
    }

    #[DataProvider('invalidNames')]
    public function testThrowsExceptionForEmptyStringAfterTrim(?string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Property($input);
    }

    public static function validNames(): \Generator
    {
        yield 'simple'         => ['foo', 'foo'];
        yield 'with whitespace'=> ['  bar  ', 'bar'];
        yield 'tabbed'         => ["\t name \t", 'name'];
        yield 'newline'        => ["\nhello\n", 'hello'];
    }

    public static function invalidNames(): \Generator
    {
        yield 'empty string'  => [''];
        yield 'spaces only'   => ['   '];
        yield 'tabs only'     => ["\t\t"];
        yield 'newlines only' => ["\n\n"];
    }
}
