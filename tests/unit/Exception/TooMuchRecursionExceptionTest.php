<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Exception;

use Hermiod\Exception\Exception;
use Hermiod\Exception\TooMuchRecursionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TooMuchRecursionException::class)]
final class TooMuchRecursionExceptionTest extends TestCase
{
    #[DataProvider('maxDepthProvider')]
    public function testNewWithVariousDepths(int $maxDepth, string $expectedMessage): void
    {
        $exception = TooMuchRecursionException::new($maxDepth);

        $this->assertInstanceOf(TooMuchRecursionException::class, $exception);
        $this->assertInstanceOf(\OverflowException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionHierarchy(): void
    {
        $exception = TooMuchRecursionException::new(10);

        $this->assertInstanceOf(\OverflowException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testMessageFormat(): void
    {
        $exception = TooMuchRecursionException::new(5);

        $message = $exception->getMessage();

        $this->assertStringStartsWith('Exceeded the maximum object depth of', $message);
        $this->assertStringContainsString('nested objects', $message);
        $this->assertStringContainsString('5', $message);
    }

    public function testMessageContainsProvidedDepth(): void
    {
        $maxDepth = 42;
        $exception = TooMuchRecursionException::new($maxDepth);

        $this->assertStringContainsString((string)$maxDepth, $exception->getMessage());
    }

    public static function maxDepthProvider(): array
    {
        return [
            'small depth' => [
                1,
                'Exceeded the maximum object depth of 1 nested objects'
            ],
            'typical depth' => [
                10,
                'Exceeded the maximum object depth of 10 nested objects'
            ],
            'large depth' => [
                100,
                'Exceeded the maximum object depth of 100 nested objects'
            ],
            'very large depth' => [
                1000,
                'Exceeded the maximum object depth of 1000 nested objects'
            ],
            'maximum integer' => [
                \PHP_INT_MAX,
                'Exceeded the maximum object depth of ' . \PHP_INT_MAX . ' nested objects'
            ],
            'zero depth' => [
                0,
                'Exceeded the maximum object depth of 0 nested objects'
            ],
            'negative depth' => [
                -1,
                'Exceeded the maximum object depth of -1 nested objects'
            ],
            'negative large depth' => [
                -100,
                'Exceeded the maximum object depth of -100 nested objects'
            ],
        ];
    }
}

