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

    #[DataProvider('nonStringCases')]
    public function testNonStringCasesThrowTypeErrorForFormat(mixed $input): void
    {
        $this->expectException(\TypeError::class);

        $strategy = new FlatCase();
        $strategy->format($input);
    }

    public static function validCases(): array
    {
        return [
            'PascalCase' => ['PascalCase', 'pascalcase'],
            'kebab-case' => ['kebab-case', 'kebabcase'],
            'snake_case' => ['snake_case', 'snakecase'],
            'camelCase' => ['camelCase', 'camelcase'],
            'COBOL-CASE' => ['COBOL-CASE', 'cobolcase'],
            'SCREAMING_SNAKE_CASE' => ['SCREAMING_SNAKE_CASE', 'screamingsnakecase'],
            'Pascal_Snake_Case' => ['Pascal_Snake_Case', 'pascalsnakecase'],
            'whitespace padded' => [' whitespace-padded ', 'whitespacepadded'],
            'whitespace within' => ['whitespace within', 'whitespacewithin'],
            'Number34Inside' => ['Number34Inside', 'number34inside'],
            'digits only (123)' => ['123', '123'],
            'digits starting (123)' => ['123something', '123something'],
            'digits ending (123)' => ['Something123', 'something123'],
        ];
    }
}
