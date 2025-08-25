<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ObjectValueIsString;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectValueIsString::class)]
final class ObjectValueIsStringTest extends TestCase
{
    #[DataProvider('provideValidValues')]
    public function testValidValuesAreAccepted(mixed $value): void
    {
        $constraint = new ObjectValueIsString();

        $this->assertTrue(
            $constraint->mapValueMatchesConstraint($value),
            'Expected string value to be accepted'
        );
    }

    #[DataProvider('provideInvalidValues')]
    public function testInvalidValuesAreRejected(mixed $value): void
    {
        $constraint = new ObjectValueIsString();

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected non-string value to be rejected'
        );
    }

    #[DataProvider('provideInvalidValues')]
    public function testMismatchExplanationIncludesDetails(mixed $value): void
    {
        $constraint = new ObjectValueIsString();

        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.value');

        $message = $constraint->getMismatchExplanation($path, $value);

        $this->assertStringContainsString('$.value', $message, 'Expected explanation to include the path');
        $this->assertStringContainsString(\gettype($value), $message, 'Expected explanation to include the value type');
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'non-empty string' => ['hello'];
        yield 'empty string' => [''];
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideInvalidValues(): iterable
    {
        yield 'int' => [42];
        yield 'float' => [3.14];
        yield 'bool true' => [true];
        yield 'bool false' => [false];
        yield 'null' => [null];
        yield 'array' => [['a']];
        yield 'object' => [new \stdClass()];
    }
}
