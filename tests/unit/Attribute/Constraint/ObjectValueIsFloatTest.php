<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ObjectValueIsFloat;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectValueIsFloat::class)]
final class ObjectValueIsFloatTest extends TestCase
{
    #[DataProvider('provideValidValues')]
    public function testValidValuesAreAccepted(mixed $value): void
    {
        $constraint = new ObjectValueIsFloat();

        $this->assertTrue(
            $constraint->mapValueMatchesConstraint($value),
            "Expected value of type " . \gettype($value) . " to be accepted"
        );
    }

    #[DataProvider('provideInvalidValues')]
    public function testInvalidValuesAreRejected(mixed $value): void
    {
        $constraint = new ObjectValueIsFloat();

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            "Expected value of type " . \gettype($value) . " to be rejected"
        );
    }

    #[DataProvider('provideInvalidValues')]
    public function testMismatchExplanationIncludesDetails(mixed $value): void
    {
        $constraint = new ObjectValueIsFloat();

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
        yield 'int' => [42];
        yield 'float' => [3.14];
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideInvalidValues(): iterable
    {
        yield 'string' => ['3.14'];
        yield 'bool true' => [true];
        yield 'bool false' => [false];
        yield 'null' => [null];
        yield 'array' => [[3.14]];
        yield 'object' => [new \stdClass()];
    }
}
