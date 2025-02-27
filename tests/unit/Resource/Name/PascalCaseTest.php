<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Name;

use Hermiod\Resource\Name\PascalCase;
use Hermiod\Resource\Name\StrategyInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PascalCase::class)]
final class PascalCaseTest extends AbstractNameTestCase
{
    public function testImplementsStrategyInterface(): void
    {
        $strategy = new PascalCase();

        $this->assertInstanceOf(
            StrategyInterface::class,
            $strategy,
            'PascalCase should implement StrategyInterface'
        );
    }

    #[DataProvider('validFormatCases')]
    #[DataProvider('emptyStringCases')]
    public function testFormat(string $input, string $expected): void
    {
        $strategy = new PascalCase();

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
        $strategy = new PascalCase();

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

        $strategy = new PascalCase();
        $strategy->format($input);
    }

    #[DataProvider('nonStringCases')]
    public function testNonStringCasesThrowTypeErrorForNormalise(mixed $input): void
    {
        $this->expectException(\TypeError::class);

        $strategy = new PascalCase();
        $strategy->normalise($input);
    }

    public static function validFormatCases(): array
    {
        return [
            'PascalCase' => ['PascalCase', 'PascalCase'],
            'kebab-case' => ['kebab-case', 'KebabCase'],
            'snake_case' => ['snake_case', 'SnakeCase'],
            'camelCase' => ['camelCase', 'CamelCase'],
            'COBOL-CASE' => ['COBOL-CASE', 'CobolCase'],
            'SCREAMING_SNAKE_CASE' => ['SCREAMING_SNAKE_CASE', 'ScreamingSnakeCase'],
            'Pascal_Snake_Case' => ['Pascal_Snake_Case', 'PascalSnakeCase'],
            'whitespace padded' => [' whitespace-padded ', 'WhitespacePadded'],
            'whitespace within' => ['whitespace within', 'WhitespaceWithin'],
            'Number34Inside' => ['Number34Inside', 'Number34inside'],
            'digits only (123)' => ['123', '123'],
            'digits starting (123)' => ['123something', '123something'],
            'digits ending (123)' => ['Something123', 'Something123'],
        ];
    }
}
