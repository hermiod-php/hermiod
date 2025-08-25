<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Name;

use Hermiod\Resource\Name\KebabCase;
use Hermiod\Resource\Name\StrategyInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(KebabCase::class)]
final class KebabCaseTest extends AbstractNameTestCase
{
    public function testImplementsStrategyInterface(): void
    {
        $strategy = new KebabCase();

        $this->assertInstanceOf(
            StrategyInterface::class,
            $strategy,
            'KebabCase should implement StrategyInterface'
        );
    }

    #[DataProvider('validFormatCases')]
    #[DataProvider('emptyStringCases')]
    public function testFormat(string $input, string $expected): void
    {
        $strategy = new KebabCase();

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

        $strategy = new KebabCase();
        $strategy->format($input);
    }

    public static function validFormatCases(): array
    {
        return [
            'PascalCase' => ['PascalCase', 'pascal-case'],
            'kebab-case' => ['kebab-case', 'kebab-case'],
            'snake_case' => ['snake_case', 'snake-case'],
            'camelCase' => ['camelCase', 'camel-case'],
            'COBOL-CASE' => ['COBOL-CASE', 'cobol-case'],
            'SCREAMING_SNAKE_CASE' => ['SCREAMING_SNAKE_CASE', 'screaming-snake-case'],
            'Pascal_Snake_Case' => ['Pascal_Snake_Case', 'pascal-snake-case'],
            'whitespace padded' => [' whitespace-padded ', 'whitespace-padded'],
            'whitespace within' => ['whitespace within', 'whitespace-within'],
            'Number34Inside' => ['Number34Inside', 'number34inside'],
            'digits only (123)' => ['123', '123'],
            'digits starting (123)' => ['123something', '123something'],
            'digits ending (123)' => ['Something123', 'something123'],
        ];
    }
}
