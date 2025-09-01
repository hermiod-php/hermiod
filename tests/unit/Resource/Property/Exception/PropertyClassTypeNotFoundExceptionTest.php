<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property\Exception;

use Hermiod\Resource\Property\Exception\Exception;
use Hermiod\Resource\Property\Exception\PropertyClassTypeNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(PropertyClassTypeNotFoundException::class)]
final class PropertyClassTypeNotFoundExceptionTest extends TestCase
{
    #[DataProvider('propertyAndTypeProvider')]
    public function testForTypedClassPropertyWithVariousInputs(string $property, string $type): void
    {
        $exception = PropertyClassTypeNotFoundException::forTypedClassProperty($property, $type);

        $this->assertInstanceOf(PropertyClassTypeNotFoundException::class, $exception);
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $expectedMessage = \sprintf(
            "Unable to locate the class '%s' used in property '%s'",
            $type,
            $property
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionHierarchy(): void
    {
        $exception = PropertyClassTypeNotFoundException::forTypedClassProperty('property', 'NonExistentClass');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(\LogicException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testMessageFormat(): void
    {
        $property = 'testProperty';
        $type = 'TestClass';
        $exception = PropertyClassTypeNotFoundException::forTypedClassProperty($property, $type);

        $message = $exception->getMessage();

        $this->assertStringStartsWith('Unable to locate the class', $message);
        $this->assertStringContainsString("'$type'", $message);
        $this->assertStringContainsString("'$property'", $message);
        $this->assertStringContainsString('used in property', $message);
    }

    public function testMessageContainsProvidedValues(): void
    {
        $property = 'userProfile';
        $type = 'App\\Models\\UserProfile';
        $exception = PropertyClassTypeNotFoundException::forTypedClassProperty($property, $type);

        $message = $exception->getMessage();

        $this->assertStringContainsString("'$property'", $message);
        $this->assertStringContainsString("'$type'", $message);
    }

    public function testWithSpecialCharacters(): void
    {
        $property = "property'with\"quotes\nand\ttabs";
        $type = "Class\\With\"Special'Chars";
        $exception = PropertyClassTypeNotFoundException::forTypedClassProperty($property, $type);

        $message = $exception->getMessage();

        $this->assertStringContainsString($property, $message);
        $this->assertStringContainsString($type, $message);
    }

    public static function propertyAndTypeProvider(): array
    {
        return [
            // Basic property and class names
            'simple property and class' => ['property', 'Class'],
            'camelCase property' => ['userProfile', 'UserProfile'],
            'snake_case property' => ['user_profile', 'UserProfile'],
            'PascalCase property' => ['UserProfile', 'UserProfileClass'],

            // Namespaced classes
            'simple namespace' => ['user', 'App\\User'],
            'deep namespace' => ['model', 'App\\Models\\User\\Profile'],
            'vendor namespace' => ['logger', 'Monolog\\Logger'],
            'psr namespace' => ['request', 'Psr\\Http\\Message\\RequestInterface'],

            // Real-world examples
            'datetime property' => ['createdAt', 'DateTime'],
            'collection property' => ['items', 'Doctrine\\Common\\Collections\\Collection'],
            'entity property' => ['user', 'App\\Entity\\User'],
            'value object' => ['email', 'App\\ValueObject\\EmailAddress'],
            'interface type' => ['repository', 'App\\Repository\\UserRepositoryInterface'],

            // Edge cases
            'single character property' => ['a', 'A'],
            'single character class' => ['property', 'X'],
            'numeric in property' => ['item1', 'Item'],
            'numeric in class' => ['item', 'Item2'],
            'underscore property' => ['_private', 'PrivateClass'],
            'underscore class' => ['helper', '_Helper'],

            // Long names
            'long property name' => ['veryLongPropertyNameThatMightBeUsedInSomeScenarios', 'Class'],
            'long class name' => ['property', 'VeryLongClassNameThatMightExistInSomeNamespace\\SubNamespace\\ActualClass'],

            // Common PHP class names that might not exist
            'non-existent built-in' => ['handler', 'NonExistentHandler'],
            'typo in class name' => ['user', 'Usre'], // Common typo
            'wrong case class' => ['config', 'config'], // lowercase class name

            // Framework-specific examples
            'laravel model' => ['post', 'App\\Models\\Post'],
            'symfony service' => ['mailer', 'Symfony\\Component\\Mailer\\MailerInterface'],
            'doctrine entity' => ['product', 'App\\Entity\\Product'],

            // Generic type-like names
            'generic collection' => ['list', 'List'],
            'generic map' => ['map', 'Map'],
            'generic set' => ['set', 'Set'],
            'generic iterator' => ['iterator', 'Iterator'],

            // Interfaces
            'interface suffix' => ['validator', 'ValidatorInterface'],
            'contract suffix' => ['cache', 'CacheContract'],
            'able suffix' => ['serializer', 'Serializable'],

            // Abstract classes
            'abstract prefix' => ['handler', 'AbstractHandler'],
            'base prefix' => ['controller', 'BaseController'],

            // Traits
            'trait suffix' => ['helper', 'HelperTrait'],
            'able trait' => ['cacheable', 'Cacheable'],

            // Special characters in names
            'property with underscore' => ['user_id', 'User'],
            'property with numbers' => ['item2', 'Item'],
            'class with numbers' => ['user', 'User2'],
            'backslash in namespace' => ['service', 'App\\Service\\EmailService'],

            // Empty and minimal cases
            'empty property' => ['', 'Class'],
            'empty class' => ['property', ''],
            'both empty' => ['', ''],

            // Special characters that might cause issues
            'property with quotes' => ["property'name", 'Class'],
            'class with quotes' => ['property', "Class'Name"],
            'property with newlines' => ["property\nname", 'Class'],
            'class with newlines' => ['property', "Class\nName"],
            'property with tabs' => ["property\tname", 'Class'],
            'class with tabs' => ['property', "Class\tName"],
        ];
    }
}

