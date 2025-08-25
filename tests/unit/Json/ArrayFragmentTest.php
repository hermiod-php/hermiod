<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Json;

use Hermiod\Json\ArrayFragment;
use Hermiod\Json\FragmentInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ArrayFragmentTest extends TestCase
{
    public function testImplementsFragmentInterface(): void
    {
        $array = [];
        $fragment = new ArrayFragment($array);

        $this->assertInstanceOf(FragmentInterface::class, $fragment, 'Must implement FragmentInterface');
    }

    public function testHasReturnsTrueWhenKeyExists(): void
    {
        $array = ['key' => 'value'];
        $fragment = new ArrayFragment($array);

        $this->assertTrue($fragment->has('key'), 'Expected has() to return true for existing key');
    }

    public function testHasReturnsFalseWhenKeyDoesNotExist(): void
    {
        $array = ['key' => 'value'];
        $fragment = new ArrayFragment($array);

        $this->assertFalse($fragment->has('missing'), 'Expected has() to return false for missing key');
    }

    public function testToArrayReturnsWrappedArray(): void
    {
        $array = ['a' => 1, 'b' => 2];
        $fragment = new ArrayFragment($array);

        $this->assertSame($array, $fragment->toArray(), 'Expected toArray() to return the full internal array');
    }

    public function testReferenceBehaviourChangesOriginalArray(): void
    {
        $array = ['foo' => 'bar'];
        $fragment = new ArrayFragment($array);

        $fragment->set('foo', 'baz');
        $fragment->set('new', 123);

        $this->assertSame('baz', $array['foo'], 'Expected modified value in original array');
        $this->assertSame(123, $array['new'], 'Expected new key to appear in original array');
    }

    #[DataProvider('provideNativeValues')]
    public function testSetAndGetHandlesAllDataTypes(string $key, mixed $value): void
    {
        $array = [];
        $fragment = new ArrayFragment($array);

        $fragment->set($key, $value);

        $this->assertTrue($fragment->has($key), 'Expected has() to return true after set');
        $this->assertSame($value, $fragment->get($key), 'Expected get() to return the exact value set');
    }

    // 👇 Data Provider
    public static function provideNativeValues(): \Generator
    {
        yield 'string'  => ['key', 'string value'];
        yield 'int'     => ['key', 42];
        yield 'float'   => ['key', 3.14];
        yield 'bool T'  => ['key', true];
        yield 'bool F'  => ['key', false];
        yield 'null'    => ['key', null];
        yield 'array'   => ['key', ['nested' => 'array']];
        yield 'object'  => ['key', (object)['foo' => 'bar']];
        yield 'callable' => ['key', fn () => 'hello'];
    }
}
