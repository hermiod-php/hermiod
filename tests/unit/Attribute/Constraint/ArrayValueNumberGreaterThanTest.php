<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ArrayConstraintInterface;
use Hermiod\Attribute\Constraint\ArrayValueNumberGreaterThan;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayValueNumberGreaterThan::class)]
final class ArrayValueNumberGreaterThanTest extends TestCase
{
    public function testImplementsArrayConstraintInterface(): void
    {
        $constraint = new ArrayValueNumberGreaterThan(10);

        $this->assertInstanceOf(ArrayConstraintInterface::class, $constraint, 'Must implement ArrayConstraintInterface');
    }

    #[DataProvider('provideValuesGreaterThanThreshold')]
    public function testValuesGreaterThanThresholdPass(mixed $value): void
    {
        $constraint = new ArrayValueNumberGreaterThan(5);

        $this->assertTrue($constraint->mapValueMatchesConstraint($value), 'Expected value greater than threshold to pass');
    }

    #[DataProvider('provideValuesLessThanOrEqualToThreshold')]
    public function testValuesLessThanOrEqualToThresholdFail(mixed $value): void
    {
        $constraint = new ArrayValueNumberGreaterThan(5);

        $this->assertFalse($constraint->mapValueMatchesConstraint($value), 'Expected value less than or equal to threshold to fail');
    }

    public function testMismatchExplanationContainsPathAndValues(): void
    {
        $constraint = new ArrayValueNumberGreaterThan(5);
        $path = $this->createMockPath('$.age');

        $message = $constraint->getMismatchExplanation($path, 3);

        $this->assertStringContainsString('$.age', $message, 'Expected path to be included in explanation');
        $this->assertStringContainsString('must be a number greater than 5', $message, 'Expected threshold to be in explanation');
        $this->assertStringContainsString('3 given', $message, 'Expected actual value to be included');
    }

    private function createMockPath(string $value): PathInterface & \PHPUnit\Framework\MockObject\MockObject
    {
        $mock = $this->createMock(PathInterface::class);
        $mock->method('__toString')->willReturn($value);

        return $mock;
    }

    public static function provideValuesGreaterThanThreshold(): \Generator
    {
        yield 'int greater' => [6];
        yield 'float greater' => [5.1];
        yield 'large float' => [100.25];
    }

    public static function provideValuesLessThanOrEqualToThreshold(): \Generator
    {
        yield 'equal int' => [5];
        yield 'equal float' => [5.0];
        yield 'less int' => [4];
        yield 'less float' => [4.999];
        yield 'zero' => [0];
    }
}
