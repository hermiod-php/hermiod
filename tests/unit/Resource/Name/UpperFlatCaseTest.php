<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Name;

use Hermiod\Resource\Name\UpperFlatCase;
use Hermiod\Resource\Name\StrategyInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(UpperFlatCase::class)]
final class UpperFlatCaseTest extends AbstractNameTestCase
{
    public function testImplementsStrategyInterface(): void
    {
        $strategy = new UpperFlatCase();

        $this->assertInstanceOf(
            StrategyInterface::class,
            $strategy,
            'UpperFlatCase should implement StrategyInterface'
        );
    }

    #[DataProvider('validFormatCases')]
    #[DataProvider('emptyStringCases')]
    public function testFormat(string $input, string $expected): void
    {
        $strategy = new UpperFlatCase();

        $this->assertSame(
            $expected,
            $strategy->format($input),
            "format('$input') should return '$expected'"
        );
    }

    #[DataProvider('nonStringCases')]
    public function testNonStringCasesThrowTypeError(mixed $input): void
    {
        $this->expectException(\TypeError::class);

        $strategy = new UpperFlatCase();
        $strategy->format($input);
    }

    public static function validFormatCases(): array
    {
        return [
            'PascalCase' => ['PascalCase', 'PASCALCASE'],
            'kebab-case' => ['kebab-case', 'KEBABCASE'],
            'snake_case' => ['snake_case', 'SNAKECASE'],
            'camelCase' => ['camelCase', 'CAMELCASE'],
            'COBOL-CASE' => ['COBOL-CASE', 'COBOLCASE'],
            'SCREAMING_SNAKE_CASE' => ['SCREAMING_SNAKE_CASE', 'SCREAMINGSNAKECASE'],
            'Pascal_Snake_Case' => ['Pascal_Snake_Case', 'PASCALSNAKECASE'],
            'whitespace padded' => [' whitespace-padded ', 'WHITESPACEPADDED'],
            'whitespace within' => ['whitespace within', 'WHITESPACEWITHIN'],
            'Number34Inside' => ['Number34Inside', 'NUMBER34INSIDE'],
            'digits only (123)' => ['123', '123'],
            'digits starting (123)' => ['123something', '123SOMETHING'],
            'digits ending (123)' => ['Something123', 'SOMETHING123'],
        ];
    }
}
