<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Path\Exception;

use Hermiod\Resource\Path\Exception\EmptyJsonPathObjectKeyException;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EmptyJsonPathObjectKeyExceptionTest extends TestCase
{
    public function testExceptionMassageIsCorrectlyFormatted(): void
    {
        $exception = EmptyJsonPathObjectKeyException::new(
            $this->createPathMock('$.foo.bar'),
            "\n\r\t"
        );

        $this->assertSame(
            "The JSON object key '\\n\\r\\t' could not be appended to $.foo.bar as it is empty.",
            $exception->getMessage(),
        );
    }

    private function createPathMock(string $path): PathInterface & MockObject
    {
        $root = $this->createMock(PathInterface::class);

        $root->method('__toString')->willReturn($path);

        return $root;
    }
}
