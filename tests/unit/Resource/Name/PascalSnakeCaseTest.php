<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Name;

use Hermiod\Resource\Name\PascalSnakeCase;
use Hermiod\Resource\Name\StrategyInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PascalSnakeCase::class)]
final class PascalSnakeCaseTest extends AbstractNameTestCase
{
    public function testImplementsStrategyInterface(): void
    {
        $strategy = new PascalSnakeCase();

        $this->assertInstanceOf(
            StrategyInterface::class,
            $strategy,
            'PascalSnakeCase should implement StrategyInterface'
        );
    }

    #[DataProvider('validFormatCases')]
    #[DataProvider('emptyStringCases')]
    public function testFormat(string $input, string $expected): void
    {
        $strategy = new PascalSnakeCase();

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
        $strategy = new PascalSnakeCase();

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

        $strategy = new PascalSnakeCase();
        $strategy->format($input);
    }

    #[DataProvider('nonStringCases')]
    public function testNonStringCasesThrowTypeErrorForNormalise(mixed $input): void
    {
        $this->expectException(\TypeError::class);

        $strategy = new PascalSnakeCase();
        $strategy->normalise($input);
    }

    public static function validFormatCases(): array
    {
        return [
            'PascalCase' => ['PascalCase', 'Pascal_Case'],
            'kebab-case' => ['kebab-case', 'Kebab_Case'],
            'snake_case' => ['snake_case', 'Snake_Case'],
            'camelCase' => ['camelCase', 'Camel_Case'],
            'COBOL-CASE' => ['COBOL-CASE', 'Cobol_Case'],
            'SCREAMING_SNAKE_CASE' => ['SCREAMING_SNAKE_CASE', 'Screaming_Snake_Case'],
            'Pascal_Snake_Case' => ['Pascal_Snake_Case', 'Pascal_Snake_Case'],
            'whitespace padded' => [' whitespace-padded ', 'Whitespace_Padded'],
            'whitespace within' => ['whitespace within', 'Whitespace_Within'],
            'Number34Inside' => ['Number34Inside', 'Number34inside'],
            'digits only (123)' => ['123', '123'],
            'digits starting (123)' => ['123something', '123something'],
            'digits ending (123)' => ['Something123', 'Something123'],
        ];
    }
}
