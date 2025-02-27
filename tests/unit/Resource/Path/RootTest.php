<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Path;

use Hermiod\Resource\Path\Exception\EmptyJsonPathObjectKeyException;
use Hermiod\Resource\Path\Root;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

#[CoversClass(Root::class)]
final class RootTest extends TestCase
{
    #[DataProvider('validObjectKeyProvider')]
    public function testWithObjectKey(string $key, string $expected): void
    {
        $root = new Root();

        $this->assertSame(
            $expected,
            $root->withObjectKey($key)->__toString(),
            'withObjectKey() should append a properly formatted object key'
        );
    }

    #[DataProvider('invalidObjectKeyValueProvider')]
    public function testInvalidObjectKeyThrows(string $key): void
    {
        $this->expectException(EmptyJsonPathObjectKeyException::class);

        $root = new Root();
        $root->withObjectKey($key);
    }

    #[DataProvider('validArrayKeyProvider')]
    public function testWithArrayKey(int $key, string $expected): void
    {
        $root = new Root();

        $this->assertSame(
            $expected,
            $root->withArrayKey($key)->__toString(),
            'withArrayKey() should append a properly formatted array key'
        );
    }

    #[Depends('testWithObjectKey')]
    #[Depends('testWithArrayKey')]
    public function testComplexPathRender(): void
    {
        $root = new Root();

        $path = $root
            ->withObjectKey('chevron')
            ->withArrayKey(7)
            ->withObjectKey('will not engage')
            ->withArrayKey(0)
            ->withObjectKey('initiate')
            ->withObjectKey('clean-up');

        $this->assertSame(
            '$.chevron[7]["will not engage"][0].initiate["clean-up"]',
            $path->__toString(),
            'Complex path did not match'
        );
    }

    public static function validObjectKeyProvider(): array
    {
        return [
            'normal string' => ['key', '$.key'],
            'utf8 string' => ['çø∂∆', '$["çø∂∆"]'],
            'trimmed input' => [' key ', '$.key'],
            'spaces' => ['this is my key', '$["this is my key"]'],
            'enclosed escape chars' => ["foo\tbar", "$[\"foo\tbar\"]"],
        ];
    }

    public static function invalidObjectKeyValueProvider(): array
    {
        return [
            'empty string' => [''],
            'tab' => ["\t"],
            'newline' => ["\r\n"],
        ];
    }

    public static function validArrayKeyProvider(): array
    {
        return [
            'zero' => [0, '$[0]'],
            'positive number' => [123, '$[123]'],
            'negative number' => [-42, '$[-42]'],
        ];
    }
}
