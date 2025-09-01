<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\StringConstraintInterface;
use Hermiod\Attribute\Constraint\StringInList;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StringInListTest extends TestCase
{
    public function testConstructorWithZeroValuesThrows(): void
    {
        $this->expectException(\TypeError::class);

        new StringInList();
    }

    public function testConstructorWithSingleValue(): void
    {
        $constraint = new StringInList('value1');

        $this->assertTrue($constraint->valueMatchesConstraint('value1'));
        $this->assertFalse($constraint->valueMatchesConstraint('value2'));
    }

    public function testConstructorWithMultipleValues(): void
    {
        $constraint = new StringInList('value1', 'value2', 'value3');

        $this->assertTrue($constraint->valueMatchesConstraint('value1'));
        $this->assertTrue($constraint->valueMatchesConstraint('value2'));
        $this->assertTrue($constraint->valueMatchesConstraint('value3'));
        $this->assertFalse($constraint->valueMatchesConstraint('value4'));
    }

    public function testValueMatchesConstraintWithStrictComparison(): void
    {
        $constraint = new StringInList('1', '2', '3');

        $this->assertTrue($constraint->valueMatchesConstraint('1'));
        $this->assertFalse($constraint->valueMatchesConstraint(1));
        $this->assertFalse($constraint->valueMatchesConstraint('01'));
    }

    public function testValueMatchesConstraintWithEmptyString(): void
    {
        $constraint = new StringInList('', 'value1');

        $this->assertTrue($constraint->valueMatchesConstraint(''));
        $this->assertTrue($constraint->valueMatchesConstraint('value1'));
        $this->assertFalse($constraint->valueMatchesConstraint('value2'));
    }

    public function testValueMatchesConstraintWithSpecialCharacters(): void
    {
        $constraint = new StringInList('value"with\'quotes', 'value\nwith\nnewlines', 'value\twith\ttabs');

        $this->assertTrue($constraint->valueMatchesConstraint('value"with\'quotes'));
        $this->assertTrue($constraint->valueMatchesConstraint('value\nwith\nnewlines'));
        $this->assertTrue($constraint->valueMatchesConstraint('value\twith\ttabs'));
        $this->assertFalse($constraint->valueMatchesConstraint('value"with"quotes'));
    }

    public function testValueMatchesConstraintWithUnicodeCharacters(): void
    {
        $constraint = new StringInList('café', 'naïve', '🚀');

        $this->assertTrue($constraint->valueMatchesConstraint('café'));
        $this->assertTrue($constraint->valueMatchesConstraint('naïve'));
        $this->assertTrue($constraint->valueMatchesConstraint('🚀'));
        $this->assertFalse($constraint->valueMatchesConstraint('cafe'));
    }

    public function testValueMatchesConstraintWithMixedTypes(): void
    {
        $constraint = new StringInList('true', 'false', '0');

        $this->assertTrue($constraint->valueMatchesConstraint('true'));
        $this->assertFalse($constraint->valueMatchesConstraint(true));
        $this->assertFalse($constraint->valueMatchesConstraint(false));
        $this->assertFalse($constraint->valueMatchesConstraint(0));
        $this->assertTrue($constraint->valueMatchesConstraint('0'));
    }

    public function testGetMismatchExplanationWithSingleValue(): void
    {
        $constraint = new StringInList('allowed');
        $path = $this->createMock(PathInterface::class);
        $path->method('__toString')->willReturn('field.name');

        $explanation = $constraint->getMismatchExplanation($path, 'invalid');

        $this->assertSame(
            "field.name must be one of ['allowed'] but 'invalid' given",
            $explanation
        );
    }

    public function testGetMismatchExplanationWithMultipleValues(): void
    {
        $constraint = new StringInList('value1', 'value2', 'value3');
        $path = $this->createMock(PathInterface::class);
        $path->method('__toString')->willReturn('nested.field');

        $explanation = $constraint->getMismatchExplanation($path, 'invalid');

        $this->assertSame(
            "nested.field must be one of ['value1', 'value2', 'value3'] but 'invalid' given",
            $explanation
        );
    }

    public function testGetMismatchExplanationWithSpecialCharactersInValues(): void
    {
        $constraint = new StringInList('value"with\'quotes', 'value\nwith\nnewlines');
        $path = $this->createMock(PathInterface::class);
        $path->method('__toString')->willReturn('field');

        $explanation = $constraint->getMismatchExplanation($path, 'invalid');

        $this->assertSame(
            "field must be one of ['value\"with'quotes', 'value\\nwith\\nnewlines'] but 'invalid' given",
            $explanation
        );
    }

    public function testGetMismatchExplanationWithSpecialCharactersInProvidedValue(): void
    {
        $constraint = new StringInList('valid');
        $path = $this->createMock(PathInterface::class);
        $path->method('__toString')->willReturn('field');

        $explanation = $constraint->getMismatchExplanation($path, 'invalid"with\'quotes');

        $this->assertSame(
            "field must be one of ['valid'] but 'invalid\"with'quotes' given",
            $explanation
        );
    }

    public function testImplementsStringConstraintInterface(): void
    {
        $constraint = new StringInList('value');

        $this->assertInstanceOf(StringConstraintInterface::class, $constraint);
    }

    public function testClassIsFinal(): void
    {
        $reflection = new \ReflectionClass(StringInList::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testClassHasAttributeAttribute(): void
    {
        $reflection = new \ReflectionClass(StringInList::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $this->assertSame(\Attribute::TARGET_PROPERTY, $attributes[0]->getArguments()[0]);
    }

    #[DataProvider('valueMatchingProvider')]
    public function testValueMatchingVariousScenarios(array $allowedValues, string $testValue, bool $expected): void
    {
        $constraint = new StringInList(...$allowedValues);

        $this->assertSame($expected, $constraint->valueMatchesConstraint($testValue));
    }

    public static function valueMatchingProvider(): array
    {
        return [
            'single value match' => [['test'], 'test', true],
            'single value no match' => [['test'], 'other', false],
            'multiple values first match' => [['first', 'second', 'third'], 'first', true],
            'multiple values middle match' => [['first', 'second', 'third'], 'second', true],
            'multiple values last match' => [['first', 'second', 'third'], 'third', true],
            'multiple values no match' => [['first', 'second', 'third'], 'fourth', false],
            'case sensitive' => [['Test'], 'test', false],
            'empty string in list' => [['', 'value'], '', true],
            'whitespace sensitive' => [['test '], 'test', false],
            'numbers as strings' => [['1', '2', '3'], '2', true],
            'special characters' => [['@#$%'], '@#$%', true],
        ];
    }

    #[DataProvider('mismatchExplanationProvider')]
    public function testMismatchExplanationVariousScenarios(array $allowedValues, string $pathString, string $invalidValue, string $expected): void
    {
        $constraint = new StringInList(...$allowedValues);
        $path = $this->createMock(PathInterface::class);
        $path->method('__toString')->willReturn($pathString);

        $explanation = $constraint->getMismatchExplanation($path, $invalidValue);

        $this->assertSame($expected, $explanation);
    }

    public static function mismatchExplanationProvider(): array
    {
        return [
            'simple case' => [
                ['valid'],
                'field',
                'invalid',
                "field must be one of ['valid'] but 'invalid' given"
            ],
            'multiple values' => [
                ['a', 'b', 'c'],
                'nested.field',
                'x',
                "nested.field must be one of ['a', 'b', 'c'] but 'x' given"
            ],
            'empty string allowed' => [
                ['', 'value'],
                'field',
                'wrong',
                "field must be one of ['', 'value'] but 'wrong' given"
            ],
            'complex path' => [
                ['option1', 'option2'],
                'root.child[0].property',
                'option3',
                "root.child[0].property must be one of ['option1', 'option2'] but 'option3' given"
            ],
        ];
    }
}

