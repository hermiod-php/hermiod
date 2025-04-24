<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Name;

use Hermiod\Resource\Name\ScreamingSnakeCase;
use Hermiod\Resource\Name\StrategyInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ScreamingSnakeCase::class)]
final class ScreaminglSnakeCaseTest extends AbstractNameTestCase
{
    public function testImplementsStrategyInterface(): void
    {
        $strategy = new ScreamingSnakeCase();

        $this->assertInstanceOf(
            StrategyInterface::class,
            $strategy,
            'ScreamingSnakeCase should implement StrategyInterface'
        );
    }

    #[DataProvider('validFormatCases')]
    #[DataProvider('emptyStringCases')]
    public function testFormat(string $input, string $expected): void
    {
        $strategy = new ScreamingSnakeCase();

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

        $strategy = new ScreamingSnakeCase();
        $strategy->format($input);
    }

    public static function validFormatCases(): array
    {
        return [
            'PascalCase' => ['PascalCase', 'PASCAL_CASE'],
            'kebab-case' => ['kebab-case', 'KEBAB_CASE'],
            'snake_case' => ['snake_case', 'SNAKE_CASE'],
            'camelCase' => ['camelCase', 'CAMEL_CASE'],
            'COBOL-CASE' => ['COBOL-CASE', 'COBOL_CASE'],
            'SCREAMING_SNAKE_CASE' => ['SCREAMING_SNAKE_CASE', 'SCREAMING_SNAKE_CASE'],
            'Pascal_Snake_Case' => ['Pascal_Snake_Case', 'PASCAL_SNAKE_CASE'],
            'whitespace padded' => [' whitespace-padded ', 'WHITESPACE_PADDED'],
            'whitespace within' => ['whitespace within', 'WHITESPACE_WITHIN'],
            'Number34Inside' => ['Number34Inside', 'NUMBER34INSIDE'],
            'digits only (123)' => ['123', '123'],
            'digits starting (123)' => ['123something', '123SOMETHING'],
            'digits ending (123)' => ['Something123', 'SOMETHING123'],
        ];
    }
}
