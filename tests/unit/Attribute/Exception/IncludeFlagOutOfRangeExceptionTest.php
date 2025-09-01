<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Exception;

use Hermiod\Attribute\Exception\Exception;
use Hermiod\Attribute\Exception\IncludeFlagOutOfRangeException;
use Hermiod\Attribute\Resource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(IncludeFlagOutOfRangeException::class)]
final class IncludeFlagOutOfRangeExceptionTest extends TestCase
{
    #[DataProvider('invalidValueProvider')]
    public function testForSuppliedValueWithInvalidValues(int $value): void
    {
        $exception = IncludeFlagOutOfRangeException::forSuppliedValue(new Resource(), $value);

        $this->assertInstanceOf(IncludeFlagOutOfRangeException::class, $exception);
        $this->assertInstanceOf(\RangeException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $message = $exception->getMessage();

        $this->assertStringContainsString("Invalid value ($value)", $message);
        $this->assertStringContainsString('Available constants:', $message);
        $this->assertStringContainsString('Resource::INCLUDE_', $message);
    }

    public function testExceptionHierarchy(): void
    {
        $exception = IncludeFlagOutOfRangeException::forSuppliedValue(new Resource(), 999);

        $this->assertInstanceOf(\RangeException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testMessageContainsResourceConstants(): void
    {
        $exception = IncludeFlagOutOfRangeException::forSuppliedValue(new Resource(), 999);

        $message = $exception->getMessage();

        $this->assertStringContainsString('Available constants:', $message);

        // Check that it contains the actual INCLUDE_ constants from Resource class
        $reflection = new \ReflectionClass(Resource::class);

        $includeConstants = \array_filter(
            $reflection->getReflectionConstants(),
            static fn(\ReflectionClassConstant $constant): bool => \str_starts_with($constant->getName(), 'INCLUDE_'),
        );

        foreach ($includeConstants as $constant) {
            $this->assertStringContainsString("Resource::{$constant->getName()}", $message);
        }
    }

    public static function invalidValueProvider(): array
    {
        return [
            'negative value' => [-1],
            'zero' => [0],
            'large positive value' => [999],
            'maximum integer' => [\PHP_INT_MAX],
            'minimum integer' => [\PHP_INT_MIN],
            'random invalid value' => [12345],
        ];
    }
}
