<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property\Exception;

use Hermiod\Resource\Property\Exception\Exception;
use Hermiod\Resource\Property\Exception\UnsupportedPropertyTypeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnsupportedPropertyTypeException::class)]
final class UnsupportedPropertyTypeExceptionTest extends TestCase
{
    #[DataProvider('typeProvider')]
    public function testNoFactoryForWithVariousTypes(string $type): void
    {
        $exception = UnsupportedPropertyTypeException::noFactoryFor($type);

        $this->assertInstanceOf(UnsupportedPropertyTypeException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $expectedMessage = \sprintf(
            "No factory is available for the PHP type '%s'",
            $type
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionHierarchy(): void
    {
        $exception = UnsupportedPropertyTypeException::noFactoryFor('unsupported');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(\LogicException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testMessageFormat(): void
    {
        $type = 'CustomType';
        $exception = UnsupportedPropertyTypeException::noFactoryFor($type);

        $message = $exception->getMessage();

        $this->assertStringStartsWith('No factory is available for the PHP type', $message);
        $this->assertStringContainsString("'$type'", $message);
    }

    public function testMessageContainsProvidedType(): void
    {
        $type = 'MyCustomClass';
        $exception = UnsupportedPropertyTypeException::noFactoryFor($type);

        $this->assertStringContainsString("'$type'", $exception->getMessage());
    }

    public function testWithEmptyString(): void
    {
        $exception = UnsupportedPropertyTypeException::noFactoryFor('');

        $this->assertStringContainsString("''", $exception->getMessage());
    }

    public function testWithSpecialCharacters(): void
    {
        $type = "Type'with\"quotes\nand\ttabs";
        $exception = UnsupportedPropertyTypeException::noFactoryFor($type);

        $this->assertStringContainsString($type, $exception->getMessage());
    }

    public static function typeProvider(): array
    {
        return [
            // Basic PHP types
            'string type' => ['string'],
            'int type' => ['int'],
            'float type' => ['float'],
            'bool type' => ['bool'],
            'array type' => ['array'],
            'object type' => ['object'],
            'callable type' => ['callable'],
            'iterable type' => ['iterable'],
            'mixed type' => ['mixed'],
            'void type' => ['void'],
            'null type' => ['null'],
            'never type' => ['never'],

            // Type aliases
            'integer alias' => ['integer'],
            'boolean alias' => ['boolean'],
            'double alias' => ['double'],
            'real alias' => ['real'],

            // Built-in classes
            'stdClass' => ['stdClass'],
            'DateTime' => ['DateTime'],
            'DateTimeImmutable' => ['DateTimeImmutable'],
            'Exception' => ['Exception'],
            'Closure' => ['Closure'],
            'Generator' => ['Generator'],
            'WeakReference' => ['WeakReference'],

            // SPL classes
            'ArrayObject' => ['ArrayObject'],
            'SplFixedArray' => ['SplFixedArray'],
            'SplObjectStorage' => ['SplObjectStorage'],
            'Iterator' => ['Iterator'],
            'IteratorAggregate' => ['IteratorAggregate'],
            'Countable' => ['Countable'],
            'Serializable' => ['Serializable'],

            // Common interfaces
            'Traversable' => ['Traversable'],
            'ArrayAccess' => ['ArrayAccess'],
            'JsonSerializable' => ['JsonSerializable'],
            'Stringable' => ['Stringable'],

            // Namespaced classes
            'simple namespace' => ['App\\User'],
            'deep namespace' => ['App\\Models\\User\\Profile'],
            'vendor namespace' => ['Monolog\\Logger'],
            'psr namespace' => ['Psr\\Http\\Message\\RequestInterface'],

            // Custom types that might not be supported
            'custom class' => ['CustomClass'],
            'custom interface' => ['CustomInterface'],
            'custom trait' => ['CustomTrait'],
            'custom enum' => ['CustomEnum'],

            // Union types (PHP 8.0+)
            'union type' => ['string|int'],
            'nullable union' => ['string|int|null'],
            'complex union' => ['string|int|float|bool'],

            // Intersection types (PHP 8.1+)
            'intersection type' => ['Countable&Iterator'],
            'complex intersection' => ['ArrayAccess&Countable&Iterator'],

            // Generic-like types (not native PHP but might be documented)
            'array of strings' => ['string[]'],
            'array of objects' => ['User[]'],
            'nested arrays' => ['string[][]'],
            'generic collection' => ['Collection<User>'],
            'generic map' => ['Map<string, User>'],

            // Framework-specific types
            'laravel collection' => ['Illuminate\\Support\\Collection'],
            'symfony request' => ['Symfony\\Component\\HttpFoundation\\Request'],
            'doctrine collection' => ['Doctrine\\Common\\Collections\\Collection'],

            // Edge cases
            'empty string' => [''],
            'single character' => ['A'],
            'numeric string' => ['123'],
            'underscore prefix' => ['_Type'],
            'double underscore' => ['__Type'],

            // Special characters
            'with quotes' => ["Type'Name"],
            'with double quotes' => ['Type"Name'],
            'with newlines' => ["Type\nName"],
            'with tabs' => ["Type\tName"],
            'with backslashes' => ['Type\\Name'],
            'with forward slashes' => ['Type/Name'],
            'with spaces' => ['Type Name'],

            // Long type names
            'very long type' => ['VeryLongTypeNameThatMightBeUsedInSomeComplexScenarios'],
            'long namespace' => ['Very\\Long\\Namespace\\Path\\To\\Some\\Class\\ThatMightExist'],

            // Resource type
            'resource type' => ['resource'],
            'specific resource' => ['resource (stream)'],

            // Pseudo-types from documentation
            'self type' => ['self'],
            'parent type' => ['parent'],
            'static type' => ['static'],
            'false type' => ['false'],
            'true type' => ['true'],

            // Types with special meanings
            'class-string' => ['class-string'],
            'callable-string' => ['callable-string'],
            'numeric-string' => ['numeric-string'],
            'non-empty-string' => ['non-empty-string'],

            // Malformed types that might be encountered
            'malformed union' => ['string|'],
            'malformed intersection' => ['Countable&'],
            'double pipe' => ['string||int'],
            'invalid characters' => ['Type@Name'],
            'html-like' => ['<Type>'],
            'markdown-like' => ['`Type`'],
        ];
    }
}

