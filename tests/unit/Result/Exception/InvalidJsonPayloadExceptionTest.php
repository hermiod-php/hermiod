<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Result\Exception;

use Hermiod\Result\Exception\InvalidJsonPayloadException;
use Hermiod\Exception\Exception;
use PHPUnit\Framework\TestCase;

final class InvalidJsonPayloadExceptionTest extends TestCase
{
    public function testImplementsExceptionInterface(): void
    {
        $exception = new InvalidJsonPayloadException('Test message');

        $this->assertInstanceOf(
            Exception::class,
            $exception,
            'InvalidJsonPayloadException should implement Exception'
        );
    }

    public function testNewCreatesExceptionWithCorrectMessage(): void
    {
        $className = 'TestClass';
        $errors = ['Error 1', 'Error 2'];

        $exception = InvalidJsonPayloadException::new($className, $errors);

        $expectedMessage = "Unable to create instance of TestClass as the supplied JSON was not valid. Errors:\nError 1\nError 2";

        $this->assertSame(
            $expectedMessage,
            $exception->getMessage(),
            'new() should create an exception with the correctly formatted message'
        );
    }
}
