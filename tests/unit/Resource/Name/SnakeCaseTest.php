<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Name;

use Hermiod\Resource\Name\SnakeCase;
use Hermiod\Resource\Name\StrategyInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(SnakeCase::class)]
final class SnakeCaseTest extends AbstractNameTestCase
{
    public function testImplementsStrategyInterface(): void
    {
        $strategy = new SnakeCase();

        $this->assertInstanceOf(
            StrategyInterface::class,
            $strategy,
            'SnakeCase should implement StrategyInterface'
        );
    }

    #[DataProvider('validFormatCases')]
    #[DataProvider('emptyStringCases')]
    public function testFormat(string $input, string $expected): void
    {
        $strategy = new SnakeCase();

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
        $strategy = new SnakeCase();

        $this->assertSame(
            $expected,
            $strategy->normalise($input),
            "normalise('$input') should return '$expected'"
        );
    }

    #[DataProvider('nonStringCases')]
    public function testNonStringCasesThrowTypeError(mixed $input): void
    {
        $this->expectException(\TypeError::class);

        $strategy = new SnakeCase();
        $strategy->format($input);
    }

    #[DataProvider('nonStringCases')]
    public function testNonStringCasesThrowTypeErrorForNormalise(mixed $input): void
    {
        $this->expectException(\TypeError::class);

        $strategy = new SnakeCase();
        $strategy->normalise($input);
    }

    public static function validFormatCases(): array
    {
        return [
            'PascalCase' => ['PascalCase', 'pascal_case'],
            'kebab-case' => ['kebab-case', 'kebab_case'],
            'snake_case' => ['snake_case', 'snake_case'],
            'camelCase' => ['camelCase', 'camel_case'],
            'COBOL-CASE' => ['COBOL-CASE', 'cobol_case'],
            'SCREAMING_SNAKE_CASE' => ['SCREAMING_SNAKE_CASE', 'screaming_snake_case'],
            'Pascal_Snake_Case' => ['Pascal_Snake_Case', 'pascal_snake_case'],
            'whitespace padded' => [' whitespace-padded ', 'whitespace_padded'],
            'whitespace within' => ['whitespace within', 'whitespace_within'],
            'Number34Inside' => ['Number34Inside', 'number34inside'],
            'digits only (123)' => ['123', '123'],
            'digits starting (123)' => ['123something', '123something'],
            'digits ending (123)' => ['Something123', 'something123'],
        ];
    }
}
