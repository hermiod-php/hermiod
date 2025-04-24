<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Name;

use PHPUnit\Framework\TestCase;

abstract class AbstractNameTestCase extends TestCase
{
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
