<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Attribute\Constraint;

use Hermiod\Attribute\Constraint\ObjectValueStringInList;
use Hermiod\Attribute\Constraint\ObjectValueConstraintInterface;
use Hermiod\Resource\Path\PathInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectValueStringInList::class)]
final class ObjectValueStringInListTest extends TestCase
{
    public function testConstructorWithZeroValuesThrows(): void
    {
        $this->expectException(\TypeError::class);

        // @phpstan-ignore-next-line - intentionally wrong usage for test
        new ObjectValueStringInList();
    }

    public function testConstructorWithSingleValue(): void
    {
        $constraint = new ObjectValueStringInList('value1');

        $this->assertTrue($constraint->mapValueMatchesConstraint('value1'));
        $this->assertFalse($constraint->mapValueMatchesConstraint('value2'));
    }

    public function testConstructorWithMultipleValues(): void
    {
        $constraint = new ObjectValueStringInList('value1', 'value2', 'value3');

        $this->assertTrue($constraint->mapValueMatchesConstraint('value1'));
        $this->assertTrue($constraint->mapValueMatchesConstraint('value2'));
        $this->assertTrue($constraint->mapValueMatchesConstraint('value3'));
        $this->assertFalse($constraint->mapValueMatchesConstraint('value4'));
    }

    public function testValueMatchesConstraintWithStrictComparison(): void
    {
        $constraint = new ObjectValueStringInList('1', '2', '3');

        $this->assertTrue($constraint->mapValueMatchesConstraint('1'));
        $this->assertFalse($constraint->mapValueMatchesConstraint(1));
        $this->assertFalse($constraint->mapValueMatchesConstraint('01'));
    }

    public function testValueMatchesConstraintWithEmptyString(): void
    {
        $constraint = new ObjectValueStringInList('', 'value1');

        $this->assertTrue($constraint->mapValueMatchesConstraint(''));
        $this->assertTrue($constraint->mapValueMatchesConstraint('value1'));
        $this->assertFalse($constraint->mapValueMatchesConstraint('value2'));
    }

    public function testValueMatchesConstraintWithSpecialCharacters(): void
    {
        $constraint = new ObjectValueStringInList('value"with\'quotes', 'value\nwith\nnewlines', 'value\twith\ttabs');

        $this->assertTrue($constraint->mapValueMatchesConstraint('value"with\'quotes'));
        $this->assertTrue($constraint->mapValueMatchesConstraint('value\nwith\nnewlines'));
        $this->assertTrue($constraint->mapValueMatchesConstraint('value\twith\ttabs'));
        $this->assertFalse($constraint->mapValueMatchesConstraint('value"with"quotes'));
    }

    public function testValueMatchesConstraintWithUnicodeCharacters(): void
    {
        $constraint = new ObjectValueStringInList('café', 'naïve', '🚀');

        $this->assertTrue($constraint->mapValueMatchesConstraint('café'));
        $this->assertTrue($constraint->mapValueMatchesConstraint('naïve'));
        $this->assertTrue($constraint->mapValueMatchesConstraint('🚀'));
        $this->assertFalse($constraint->mapValueMatchesConstraint('cafe'));
    }

    public function testValueMatchesConstraintWithMixedTypes(): void
    {
        $constraint = new ObjectValueStringInList('true', 'false', '0');

        $this->assertTrue($constraint->mapValueMatchesConstraint('true'));
        $this->assertFalse($constraint->mapValueMatchesConstraint(true));
        $this->assertFalse($constraint->mapValueMatchesConstraint(false));
        $this->assertFalse($constraint->mapValueMatchesConstraint(0));
        $this->assertTrue($constraint->mapValueMatchesConstraint('0'));
    }

    public function testGetMismatchExplanationWithSingleValue(): void
    {
        $constraint = new ObjectValueStringInList('allowed');
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
        $constraint = new ObjectValueStringInList('value1', 'value2', 'value3');
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
        $constraint = new ObjectValueStringInList('value"with\'quotes', 'value\nwith\nnewlines');
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
        $constraint = new ObjectValueStringInList('valid');
        $path = $this->createMock(PathInterface::class);
        $path->method('__toString')->willReturn('field');

        $explanation = $constraint->getMismatchExplanation($path, 'invalid"with\'quotes');

        $this->assertSame(
            "field must be one of ['valid'] but 'invalid\"with'quotes' given",
            $explanation
        );
    }

    public function testImplementsObjectValueConstraintInterface(): void
    {
        $constraint = new ObjectValueStringInList('value');

        $this->assertInstanceOf(ObjectValueConstraintInterface::class, $constraint);
    }

    public function testClassIsFinal(): void
    {
        $reflection = new \ReflectionClass(ObjectValueStringInList::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testClassHasAttributeAttribute(): void
    {
        $reflection = new \ReflectionClass(ObjectValueStringInList::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $this->assertSame(\Attribute::TARGET_PROPERTY, $attributes[0]->getArguments()[0]);
    }

    #[DataProvider('valueMatchingProvider')]
    public function testValueMatchingVariousScenarios(array $allowedValues, string $testValue, bool $expected): void
    {
        $constraint = new ObjectValueStringInList(...$allowedValues);

        $this->assertSame($expected, $constraint->mapValueMatchesConstraint($testValue));
    }

    #[DataProvider('nonStringErrorMessageValueProvider')]
    public function testCanFormatErrorWithNonStringValue(mixed $value, string $type): void
    {
        $constraint = new ObjectValueStringInList('red');
        $path = $this->createMock(PathInterface::class);

        $this->assertStringContainsString(
            "but $type given",
            $constraint->getMismatchExplanation($path, $value),
        );
    }

    public static function nonStringErrorMessageValueProvider(): array
    {
        return [
            'integer' => [42, 'int'],
            'float' => [3.14, 'float'],
            'boolean true' => [true, 'bool'],
            'boolean false' => [false, 'bool'],
            'null' => [null, 'null'],
            'array' => [[1, 2, 3], 'array'],
            'object' => [new \stdClass(), 'object'],
        ];
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
        $constraint = new ObjectValueStringInList(...$allowedValues);
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

