<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Name;

use Hermiod\Resource\Name\CamelCase;
use Hermiod\Resource\Name\StrategyInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CamelCase::class)]
final class CamelCaseTest extends AbstractNameTestCase
{
    public function testImplementsStrategyInterface(): void
    {
        $strategy = new CamelCase();

        $this->assertInstanceOf(
            StrategyInterface::class,
            $strategy,
            'CamelCase should implement StrategyInterface'
        );
    }

    #[DataProvider('validFormatCases')]
    #[DataProvider('emptyStringCases')]
    public function testFormat(string $input, string $expected): void
    {
        $strategy = new CamelCase();

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

        $strategy = new CamelCase();
        $strategy->format($input);
    }

    public static function validFormatCases(): array
    {
        return [
            'PascalCase' => ['PascalCase', 'pascalCase'],
            'kebab-case' => ['kebab-case', 'kebabCase'],
            'snake_case' => ['snake_case', 'snakeCase'],
            'camelCase' => ['camelCase', 'camelCase'],
            'COBOL-CASE' => ['COBOL-CASE', 'cobolCase'],
            'SCREAMING_SNAKE_CASE' => ['SCREAMING_SNAKE_CASE', 'screamingSnakeCase'],
            'Pascal_Snake_Case' => ['Pascal_Snake_Case', 'pascalSnakeCase'],
            'whitespace padded' => [' whitespace-padded ', 'whitespacePadded'],
            'whitespace within' => ['whitespace within', 'whitespaceWithin'],
            'Number34Inside' => ['Number34Inside', 'number34inside'],
            'digits only (123)' => ['123', '123'],
            'digits starting (123)' => ['123something', '123something'],
            'digits ending (123)' => ['Something123', 'something123'],
        ];
    }
}
