<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Name;

use Hermiod\Resource\Name\TrainCase;
use Hermiod\Resource\Name\StrategyInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(TrainCase::class)]
final class TrainCaseTest extends AbstractNameTestCase
{
    public function testImplementsStrategyInterface(): void
    {
        $strategy = new TrainCase();

        $this->assertInstanceOf(
            StrategyInterface::class,
            $strategy,
            'TrainCase should implement StrategyInterface'
        );
    }

    #[DataProvider('validFormatCases')]
    #[DataProvider('emptyStringCases')]
    public function testFormat(string $input, string $expected): void
    {
        $strategy = new TrainCase();

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
        $strategy = new TrainCase();

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

        $strategy = new TrainCase();
        $strategy->format($input);
    }

    #[DataProvider('nonStringCases')]
    public function testNonStringCasesThrowTypeErrorForNormalise(mixed $input): void
    {
        $this->expectException(\TypeError::class);

        $strategy = new TrainCase();
        $strategy->normalise($input);
    }

    public static function validFormatCases(): array
    {
        return [
            'PascalCase' => ['PascalCase', 'Pascal-Case'],
            'kebab-case' => ['kebab-case', 'Kebab-Case'],
            'snake_case' => ['snake_case', 'Snake-Case'],
            'camelCase' => ['camelCase', 'Camel-Case'],
            'COBOL-CASE' => ['COBOL-CASE', 'Cobol-Case'],
            'SCREAMING_SNAKE_CASE' => ['SCREAMING_SNAKE_CASE', 'Screaming-Snake-Case'],
            'Pascal_Snake_Case' => ['Pascal_Snake_Case', 'Pascal-Snake-Case'],
            'whitespace padded' => [' whitespace-padded ', 'Whitespace-Padded'],
            'whitespace within' => ['whitespace within', 'Whitespace-Within'],
            'Number34Inside' => ['Number34Inside', 'Number34inside'],
            'digits only (123)' => ['123', '123'],
            'digits starting (123)' => ['123something', '123something'],
            'digits ending (123)' => ['Something123', 'Something123'],
        ];
    }
}
