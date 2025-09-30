<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property;

/**
 * Provides test cases for invalid PHP property names
 */
trait InvalidPhpPropertyNameProviderTrait
{
    /**
     * @return array<string, array{name: string}>
     */
    public static function invalidPhpPropertyNameProvider(): array
    {
        return [
            'empty string' => [
                'name' => '',
            ],
            'starts with number' => [
                'name' => '123property',
            ],
            'starts with dash' => [
                'name' => '-property',
            ],
            'contains space' => [
                'name' => 'property name',
            ],
            'contains hyphen' => [
                'name' => 'property-name',
            ],
            'contains dot' => [
                'name' => 'property.name',
            ],
            'contains special characters' => [
                'name' => 'property@name',
            ],
            'contains brackets' => [
                'name' => 'property[name]',
            ],
            'contains parentheses' => [
                'name' => 'property(name)',
            ],
            'contains plus sign' => [
                'name' => 'property+name',
            ],
            'contains equals sign' => [
                'name' => 'property=name',
            ],
            'contains question mark' => [
                'name' => 'property?name',
            ],
            'contains exclamation mark' => [
                'name' => 'property!name',
            ],
            'contains hash' => [
                'name' => 'property#name',
            ],
            'contains dollar sign' => [
                'name' => 'property$name',
            ],
            'contains percent' => [
                'name' => 'property%name',
            ],
            'contains ampersand' => [
                'name' => 'property&name',
            ],
            'contains asterisk' => [
                'name' => 'property*name',
            ],
            'contains forward slash' => [
                'name' => 'property/name',
            ],
            'contains backslash' => [
                'name' => 'property\\name',
            ],
            'contains pipe' => [
                'name' => 'property|name',
            ],
            'contains colon' => [
                'name' => 'property:name',
            ],
            'contains semicolon' => [
                'name' => 'property;name',
            ],
            'contains comma' => [
                'name' => 'property,name',
            ],
            'contains less than' => [
                'name' => 'property<name',
            ],
            'contains greater than' => [
                'name' => 'property>name',
            ],
            'contains quotes' => [
                'name' => 'property"name',
            ],
            'contains single quote' => [
                'name' => "property'name",
            ],
            'contains backtick' => [
                'name' => 'property`name',
            ],
            'contains tilde' => [
                'name' => 'property~name',
            ],
            'contains curly braces' => [
                'name' => 'property{name}',
            ],
            'only whitespace' => [
                'name' => '   ',
            ],
            'tab character' => [
                'name' => "property\tname",
            ],
            'newline character' => [
                'name' => "property\nname",
            ],
            'carriage return' => [
                'name' => "property\rname",
            ],
            'null byte' => [
                'name' => "property\0name",
            ],
        ];
    }
}
