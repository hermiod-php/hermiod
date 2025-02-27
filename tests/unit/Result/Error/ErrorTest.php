<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Result\Error;

use Hermiod\Result\Error\Error;
use Hermiod\Result\Error\ErrorInterface;
use JsonSerializable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Error::class)]
final class ErrorTest extends TestCase
{
    #[DataProvider('invalidMessageProvider')]
    public function testConstructorRejectsInvalidMessage(mixed $invalidMessage): void
    {
        $this->expectException(\TypeError::class);
        new Error($invalidMessage);
    }

    public function testGetMessage(): void
    {
        $message = 'Test error message';

        $error = new Error($message);

        $this->assertSame(
            $message,
            $error->getMessage(),
            'getMessage() should return the message passed to the constructor'
        );
    }

    public function testJsonSerialize(): void
    {
        $message = 'JSON error message';

        $error = new Error($message);

        $this->assertSame(
            $message,
            $error->jsonSerialize(),
            'jsonSerialize() should return the same message as getMessage()'
        );
    }

    public function testImplementsInterfaces(): void
    {
        $error = new Error('Any message');

        $this->assertInstanceOf(
            ErrorInterface::class,
            $error,
            'Error should implement ErrorInterface'
        );

        $this->assertInstanceOf(
            JsonSerializable::class,
            $error,
            'Error should implement JsonSerializable'
        );
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidMessageProvider(): array
    {
        return [
            'integer' => [123],
            'float' => [1.23],
            'array' => [['message']],
            'object' => [(object) ['message' => 'text']],
            'bool' => [true],
            'null' => [null],
        ];
    }
}
