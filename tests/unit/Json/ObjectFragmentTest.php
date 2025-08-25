<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Json;

use Hermiod\Json\ObjectFragment;
use Hermiod\Json\FragmentInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ObjectFragmentTest extends TestCase
{
    public function testImplementsFragmentInterface(): void
    {
        $object = new \stdClass();
        $fragment = new ObjectFragment($object);

        $this->assertInstanceOf(FragmentInterface::class, $fragment, 'Must implement FragmentInterface');
    }

    public function testHasReturnsTrueWhenPropertyExists(): void
    {
        $object = new \stdClass();
        $object->key = 'value';
        $fragment = new ObjectFragment($object);

        $this->assertTrue($fragment->has('key'), 'Expected has() to return true for existing property');
    }

    public function testHasReturnsFalseWhenPropertyDoesNotExist(): void
    {
        $object = new \stdClass();
        $object->existing = 'value';
        $fragment = new ObjectFragment($object);

        $this->assertFalse($fragment->has('missing'), 'Expected has() to return false for missing property');
    }

    public function testToArrayReturnsObjectProperties(): void
    {
        $object = new \stdClass();
        $object->a = 1;
        $object->b = 2;

        $fragment = new ObjectFragment($object);

        $this->assertSame(['a' => 1, 'b' => 2], $fragment->toArray(), 'Expected toArray() to return all object properties');
    }

    public function testReferenceBehaviourChangesOriginalObject(): void
    {
        $object = new \stdClass();
        $object->foo = 'bar';

        $fragment = new ObjectFragment($object);

        $fragment->set('foo', 'baz');
        $fragment->set('new', 123);

        $this->assertSame('baz', $object->foo, 'Expected modified value in original object');
        $this->assertSame(123, $object->new, 'Expected new property to appear in original object');
    }

    #[DataProvider('provideNativeValues')]
    public function testSetAndGetHandlesAllDataTypes(string $key, mixed $value): void
    {
        $object = new \stdClass();
        $fragment = new ObjectFragment($object);

        $fragment->set($key, $value);

        $this->assertTrue($fragment->has($key), 'Expected has() to return true after set');
        $this->assertSame($value, $fragment->get($key), 'Expected get() to return the exact value set');
    }

    // 👇 Data Provider
    public static function provideNativeValues(): \Generator
    {
        yield 'string'    => ['key', 'string value'];
        yield 'int'       => ['key', 42];
        yield 'float'     => ['key', 3.14];
        yield 'bool T'    => ['key', true];
        yield 'bool F'    => ['key', false];
        yield 'null'      => ['key', null];
        yield 'array'     => ['key', ['nested' => 'array']];
        yield 'object'    => ['key', (object)['foo' => 'bar']];
        yield 'callable'  => ['key', fn () => 'hello'];
    }
}
