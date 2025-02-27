<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Name;

use PHPUnit\Framework\TestCase;

abstract class AbstractNameTestCase extends TestCase
{
    public static function validNormaliseCases(): array
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

    public static function emptyStringCases(): array
    {
        return [
            'empty string' => ['', ''],
            'null byte' => ["\0", ''],
            'tabs' => ["\t", ''],
            'whitespace' => ["   ", ''],
        ];
    }

    public static function nonStringCases(): array
    {
        return [
            'null' => [null],
            'int' => [123],
            'float' => [12.34],
            'array' => [['array']],
            'object' => [(object) ['prop' => 'value']],
            'bool true' => [true],
            'bool false' => [false],
        ];
    }
}
