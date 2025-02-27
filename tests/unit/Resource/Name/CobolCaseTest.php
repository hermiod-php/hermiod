<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Name;

use Hermiod\Resource\Name\CobolCase;
use Hermiod\Resource\Name\StrategyInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CobolCase::class)]
final class CobolCaseTest extends AbstractNameTestCase
{
    public function testImplementsStrategyInterface(): void
    {
        $strategy = new CobolCase();

        $this->assertInstanceOf(
            StrategyInterface::class,
            $strategy,
            'CobolCase should implement StrategyInterface'
        );
    }

    #[DataProvider('validFormatCases')]
    #[DataProvider('emptyStringCases')]
    public function testFormat(string $input, string $expected): void
    {
        $strategy = new CobolCase();

        $this->assertSame(
            $expected,
            $strategy->format($input),
            "format('$input') should return '$expected'"
        );
    }

    #[DataProvider('validNormaliseCases')]
    #[DataProvider('emptyStringCases')]
    public function testNormalise(string $input, string $expected): void
    {
        $strategy = new CobolCase();

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

        $strategy = new CobolCase();
        $strategy->format($input);
    }

    #[DataProvider('nonStringCases')]
    public function testNonStringCasesThrowTypeErrorForNormalise(mixed $input): void
    {
        $this->expectException(\TypeError::class);

        $strategy = new CobolCase();
        $strategy->normalise($input);
    }

    public static function validFormatCases(): array
    {
        return [
            'PascalCase' => ['PascalCase', 'PASCAL-CASE'],
            'kebab-case' => ['kebab-case', 'KEBAB-CASE'],
            'snake_case' => ['snake_case', 'SNAKE-CASE'],
            'camelCase' => ['camelCase', 'CAMEL-CASE'],
            'COBOL-CASE' => ['COBOL-CASE', 'COBOL-CASE'],
            'SCREAMING_SNAKE_CASE' => ['SCREAMING_SNAKE_CASE', 'SCREAMING-SNAKE-CASE'],
            'Pascal_Snake_Case' => ['Pascal_Snake_Case', 'PASCAL-SNAKE-CASE'],
            'whitespace padded' => [' whitespace-padded ', 'WHITESPACE-PADDED'],
            'whitespace within' => ['whitespace within', 'WHITESPACE-WITHIN'],
            'Number34Inside' => ['Number34Inside', 'NUMBER34INSIDE'],
            'digits only (123)' => ['123', '123'],
            'digits starting (123)' => ['123something', '123SOMETHING'],
            'digits ending (123)' => ['Something123', 'SOMETHING123'],
        ];
    }
}
