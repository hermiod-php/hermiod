<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Name;

use Hermiod\Resource\Name\CachedNamingStrategy;
use Hermiod\Resource\Name\StrategyInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(CachedNamingStrategy::class)]
final class CachedNamingStrategyTest extends TestCase
{
    public function testImplementsStrategyInterface(): void
    {
        $cache = new CachedNamingStrategy(
            $this->createStrategyMock(),
        );

        $this->assertInstanceOf(
            StrategyInterface::class,
            $cache,
            'CachedNamingStrategy should implement StrategyInterface'
        );
    }

    public function testSuccessiveFormatCallAreCached(): void
    {
        $strategy = $this->createStrategyMock();

        $value = 'foo bar-baz FOO_BAR';
        $formatted = 'foo-bar-baz-foo-bar';

        $strategy
            ->expects($this->once())
            ->method('format')
            ->with($value)
            ->willReturn($formatted);

        $cache = new CachedNamingStrategy($strategy);

        $iterations = 5;

        while ($iterations--) {
            $this->assertSame($formatted, $cache->format($value));
        }
    }

    public function testCanWrapOtherStrategies()
    {
        $strategy = $this->createStrategyMock();

        $strategy
            ->expects($this->once())
            ->method('format');

        $cache = CachedNamingStrategy::wrap($strategy);

        $this->assertInstanceOf(StrategyInterface::class, $cache);

        $this->assertNotSame($strategy, $cache);

        $this->assertInstanceOf(CachedNamingStrategy::class, $cache);

        $cache->format('test');
    }

    public function testDoesNotWrapSelf()
    {
        $strategy = new CachedNamingStrategy(
            $inner = $this->createStrategyMock()
        );

        $inner
            ->expects($this->once())
            ->method('format');

        $cache = CachedNamingStrategy::wrap($strategy);

        $this->assertInstanceOf(StrategyInterface::class, $cache);

        $this->assertSame($strategy, $cache);

        $cache->format('test');
    }

    public function testDifferentFormatCallsDelegateToStrategy(): void
    {
        $strategy = $this->createStrategyMock();

        $strategy
            ->expects($this->exactly(3))
            ->method('format')
            ->willReturnCallback(function (string $value): string {
                return \strtoupper($value);
            });

        $cache = new CachedNamingStrategy($strategy);

        /**
         * All input values are treated as unique, as format parsing could
         * differ between implementations
         */
        $this->assertSame('FOO', $cache->format('foo'));
        $this->assertSame('FOO', $cache->format('Foo'));
        $this->assertSame('FOO', $cache->format('fOo'));
    }

    public function testDifferentNormaliseCallsDelegateToStrategy(): void
    {
        $strategy = $this->createStrategyMock();

        $strategy
            ->expects($this->exactly(3))
            ->method('format')
            ->willReturnCallback(function (string $value): string {
                return \strtolower($value);
            });

        $cache = new CachedNamingStrategy($strategy);

        /**
         * All input values are treated as unique, as normalisation parsing
         * is up to the wrapped strategy
         */
        $this->assertSame('foo', $cache->format('FOO'));
        $this->assertSame('foo', $cache->format('Foo'));
        $this->assertSame('foo', $cache->format('fOo'));
    }

    private function createStrategyMock(): StrategyInterface & MockObject
    {
        return $this->createMock(StrategyInterface::class);
    }
}
