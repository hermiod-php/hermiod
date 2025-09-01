<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property\Resolver\Exception;

use Hermiod\Resource\Property\Resolver\Exception\Exception;
use Hermiod\Resource\Property\Resolver\Exception\InterfaceNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InterfaceNotFoundException::class)]
final class InterfaceNotFoundExceptionTest extends TestCase
{
    #[DataProvider('interfaceProvider')]
    public function testForWithVariousInterfaces(string $interface): void
    {
        $exception = InterfaceNotFoundException::for($interface);

        $this->assertInstanceOf(InterfaceNotFoundException::class, $exception);
        $this->assertInstanceOf(\DomainException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $expectedMessage = \sprintf('Interface %s not found', $interface);

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionHierarchy(): void
    {
        $exception = InterfaceNotFoundException::for('NonExistentInterface');

        $this->assertInstanceOf(\DomainException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testMessageFormat(): void
    {
        $interface = 'TestInterface';
        $exception = InterfaceNotFoundException::for($interface);

        $message = $exception->getMessage();

        $this->assertStringStartsWith('Interface', $message);
        $this->assertStringContainsString($interface, $message);
        $this->assertStringEndsWith('not found', $message);
    }

    public function testMessageContainsProvidedInterface(): void
    {
        $interface = 'MyCustomInterface';
        $exception = InterfaceNotFoundException::for($interface);

        $this->assertStringContainsString($interface, $exception->getMessage());
    }

    public function testWithEmptyString(): void
    {
        $exception = InterfaceNotFoundException::for('');

        $this->assertSame('Interface  not found', $exception->getMessage());
    }

    public function testWithSpecialCharacters(): void
    {
        $interface = "Interface'with\"quotes\nand\ttabs";
        $exception = InterfaceNotFoundException::for($interface);

        $this->assertStringContainsString($interface, $exception->getMessage());
    }

    public static function interfaceProvider(): array
    {
        return [
            // Built-in PHP interfaces
            'Iterator' => ['Iterator'],
            'IteratorAggregate' => ['IteratorAggregate'],
            'Traversable' => ['Traversable'],
            'ArrayAccess' => ['ArrayAccess'],
            'Countable' => ['Countable'],
            'Serializable' => ['Serializable'],
            'JsonSerializable' => ['JsonSerializable'],
            'Stringable' => ['Stringable'],
            'Throwable' => ['Throwable'],

            // PSR interfaces
            'LoggerInterface' => ['Psr\\Log\\LoggerInterface'],
            'RequestInterface' => ['Psr\\Http\\Message\\RequestInterface'],
            'ResponseInterface' => ['Psr\\Http\\Message\\ResponseInterface'],
            'CacheInterface' => ['Psr\\Cache\\CacheItemPoolInterface'],
            'ContainerInterface' => ['Psr\\Container\\ContainerInterface'],

            // Custom application interfaces
            'UserRepositoryInterface' => ['App\\Repository\\UserRepositoryInterface'],
            'EmailServiceInterface' => ['App\\Service\\EmailServiceInterface'],
            'PaymentGatewayInterface' => ['App\\Payment\\PaymentGatewayInterface'],
            'CacheManagerInterface' => ['App\\Cache\\CacheManagerInterface'],

            // Namespaced interfaces
            'simple namespace' => ['App\\UserInterface'],
            'deep namespace' => ['App\\Models\\User\\UserInterface'],
            'vendor namespace' => ['Vendor\\Package\\SomeInterface'],
            'framework namespace' => ['Symfony\\Component\\HttpFoundation\\RequestInterface'],

            // Common interface naming patterns
            'interface suffix' => ['ValidatorInterface'],
            'contract suffix' => ['UserContract'],
            'able suffix' => ['Cacheable'],
            'ible suffix' => ['Accessible'],

            // Single character and minimal names
            'single character' => ['I'],
            'two characters' => ['IA'],
            'minimal interface' => ['Interface'],

            // Edge cases
            'empty string' => [''],
            'underscore prefix' => ['_Interface'],
            'double underscore' => ['__Interface'],
            'numeric suffix' => ['Interface1'],
            'mixed case' => ['myInterface'],
            'all caps' => ['INTERFACE'],
            'camelCase' => ['someInterface'],
            'PascalCase' => ['SomeInterface'],
            'snake_case' => ['some_interface'],

            // Long interface names
            'very long interface' => ['VeryLongInterfaceNameThatMightBeUsedInSomeComplexBusinessLogic'],
            'long namespace' => ['Very\\Long\\Namespace\\Path\\To\\Some\\Interface\\ThatMightExist'],

            // Special characters
            'with quotes' => ["Interface'Name"],
            'with double quotes' => ['Interface"Name'],
            'with newlines' => ["Interface\nName"],
            'with tabs' => ["Interface\tName"],
            'with backslashes' => ['Interface\\Name'],
            'with spaces' => ['Interface Name'],

            // Framework-specific interfaces
            'Laravel contract' => ['Illuminate\\Contracts\\Cache\\Repository'],
            'Symfony interface' => ['Symfony\\Component\\EventDispatcher\\EventDispatcherInterface'],
            'Doctrine interface' => ['Doctrine\\ORM\\EntityManagerInterface'],
            'Monolog interface' => ['Monolog\\Handler\\HandlerInterface'],

            // Domain-specific interfaces
            'repository pattern' => ['UserRepositoryInterface'],
            'service pattern' => ['EmailServiceInterface'],
            'factory pattern' => ['UserFactoryInterface'],
            'strategy pattern' => ['PaymentStrategyInterface'],
            'observer pattern' => ['ObserverInterface'],
            'command pattern' => ['CommandInterface'],
            'specification pattern' => ['SpecificationInterface'],

            // Generic type-like interfaces
            'collection interface' => ['CollectionInterface'],
            'map interface' => ['MapInterface'],
            'set interface' => ['SetInterface'],
            'list interface' => ['ListInterface'],
            'queue interface' => ['QueueInterface'],
            'stack interface' => ['StackInterface'],

            // Malformed but possible names
            'starts with number' => ['1Interface'],
            'contains special chars' => ['Interface@Name'],
            'html-like' => ['<Interface>'],
            'markdown-like' => ['`Interface`'],
            'parentheses' => ['Interface(Name)'],
            'brackets' => ['Interface[Name]'],
            'braces' => ['Interface{Name}'],
        ];
    }
}

