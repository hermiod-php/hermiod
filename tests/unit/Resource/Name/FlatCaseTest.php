<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Name;

use Hermiod\Resource\Name\FlatCase;
use Hermiod\Resource\Name\StrategyInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(FlatCase::class)]
final class FlatCaseTest extends AbstractNameTestCase
{
    public function testImplementsStrategyInterface(): void
    {
        $strategy = new FlatCase();

        $this->assertInstanceOf(
            StrategyInterface::class,
            $strategy,
            'FlatCase should implement StrategyInterface'
        );
    }

    #[DataProvider('validCases')]
    #[DataProvider('emptyStringCases')]
    public function testFormat(string $input, string $expected): void
    {
        $strategy = new FlatCase();

        $this->assertSame(
            $expected,
            $strategy->format($input),
            "format('$input') should return '$expected'"
        );
    }

    #[DataProvider('validCases')]
    #[DataProvider('emptyStringCases')]
    public function testNormalise(string $input, string $expected): void
    {
        $strategy = new FlatCase();

        $this->assertSame(
            $expected,
            $strategy->normalise($input),
            "normalise('$input') should return '$expected'"
        );
    }

    #[DataProvider('nonStringCases')]
    public function testNonStringCasesThrowTypeErrorForFormat(mixed $input): void
    {
        $this->expectException(\TypeError::class);

        $strategy = new FlatCase();
        $strategy->format($input);
    }

    #[DataProvider('nonStringCases')]
    public function testNonStringCasesThrowTypeErrorForNormalise(mixed $input): void
    {
        $this->expectException(\TypeError::class);

        $strategy = new FlatCase();
        $strategy->normalise($input);
    }

    public static function validCases(): array
    {
        return self::validNormaliseCases();
    }
}
