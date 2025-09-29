<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ObjectValueIsInteger;
use Hermiod\Resource\Path\PathInterface;
use Hermiod\Traits\JsonCompatibleTypeName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectValueIsInteger::class)]
final class ObjectValueIsIntegerTest extends TestCase
{
    use JsonCompatibleTypeName;

    #[DataProvider('provideValidValues')]
    public function testValidValuesAreAccepted(mixed $value): void
    {
        $constraint = new ObjectValueIsInteger();

        $this->assertTrue(
            $constraint->mapValueMatchesConstraint($value),
            'Expected value of type ' . \gettype($value) . ' to be accepted'
        );
    }

    #[DataProvider('provideInvalidValues')]
    public function testInvalidValuesAreRejected(mixed $value): void
    {
        $constraint = new ObjectValueIsInteger();

        $this->assertFalse(
            $constraint->mapValueMatchesConstraint($value),
            'Expected value of type ' . \gettype($value) . ' to be rejected'
        );
    }

    #[DataProvider('provideInvalidValues')]
    public function testMismatchExplanationIncludesDetails(mixed $value): void
    {
        $constraint = new ObjectValueIsInteger();

        $path = $this->createMock(PathInterface::class);
        $path
            ->method('__toString')
            ->willReturn('$.value');

        $message = $constraint->getMismatchExplanation($path, $value);

        $this->assertStringContainsString('$.value', $message, 'Expected explanation to include the path');
        $this->assertStringContainsString($this->getTypeName($value), $message, 'Expected explanation to include the value type');
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'zero' => [0];
        yield 'positive integer' => [42];
        yield 'negative integer' => [-7];
    }

    /**
     * @return iterable<string, array{0: mixed}>
     */
    public static function provideInvalidValues(): iterable
    {
        yield 'float' => [3.14];
        yield 'string' => ['42'];
        yield 'bool true' => [true];
        yield 'bool false' => [false];
        yield 'null' => [null];
        yield 'array' => [[1]];
        yield 'object' => [new \stdClass()];
    }
}
