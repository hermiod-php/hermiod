<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property\Resolver\Exception;

use Hermiod\Resource\Property\Resolver\Exception\Exception;
use Hermiod\Resource\Property\Resolver\Exception\ResolvedClassNameException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResolvedClassNameException::class)]
final class ResolvedClassNameExceptionTest extends TestCase
{
    #[DataProvider('noResolverForProvider')]
    public function testNoResolverForWithVariousInputs(string $interface, array $possible, string $expectedPossibleList): void
    {
        $exception = ResolvedClassNameException::noResolverFor($interface, $possible);

        $this->assertInstanceOf(ResolvedClassNameException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $expectedMessage = \sprintf(
            "No resolver has been mapped for %s. Mapped interfaces [%s]",
            $interface,
            $expectedPossibleList
        );
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    #[DataProvider('didNotResolveToStringProvider')]
    public function testDidNotResolveToStringWithVariousTypes(string $interface, mixed $value, string $expectedType): void
    {
        $exception = ResolvedClassNameException::didNotResolveToString($interface, $value);

        $this->assertInstanceOf(ResolvedClassNameException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $expectedMessage = \sprintf(
            "The callable resolver for %s did not resolve to a class string. %s returned.",
            $interface,
            $expectedType
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    #[DataProvider('classIsNotAnImplementationOfProvider')]
    public function testClassIsNotAnImplementationOfWithVariousInputs(string $interface, string $class): void
    {
        $exception = ResolvedClassNameException::classIsNotAnImplementationOf($interface, $class);

        $this->assertInstanceOf(ResolvedClassNameException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);

        $expectedMessage = \sprintf(
            "Resolved class %s is not an implementation of the interface %s",
            $class,
            $interface
        );
        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function testExceptionHierarchy(): void
    {
        $exception = ResolvedClassNameException::noResolverFor('Interface', []);

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
    }

    public function testNoResolverForMessageFormat(): void
    {
        $interface = 'TestInterface';
        $possible = ['Interface1', 'Interface2'];
        $exception = ResolvedClassNameException::noResolverFor($interface, $possible);

        $message = $exception->getMessage();
        $this->assertStringStartsWith('No resolver has been mapped for', $message);
        $this->assertStringContainsString($interface, $message);
        $this->assertStringContainsString('Mapped interfaces', $message);
        $this->assertStringContainsString('[', $message);
        $this->assertStringContainsString(']', $message);
    }

    public function testDidNotResolveToStringMessageFormat(): void
    {
        $interface = 'TestInterface';
        $value = 123;
        $exception = ResolvedClassNameException::didNotResolveToString($interface, $value);

        $message = $exception->getMessage();
        $this->assertStringStartsWith('The callable resolver for', $message);
        $this->assertStringContainsString($interface, $message);
        $this->assertStringContainsString('did not resolve to a class string', $message);
        $this->assertStringContainsString('returned', $message);
    }

    public function testClassIsNotAnImplementationOfMessageFormat(): void
    {
        $interface = 'TestInterface';
        $class = 'TestClass';
        $exception = ResolvedClassNameException::classIsNotAnImplementationOf($interface, $class);

        $message = $exception->getMessage();
        $this->assertStringStartsWith('Resolved class', $message);
        $this->assertStringContainsString($class, $message);
        $this->assertStringContainsString($interface, $message);
        $this->assertStringContainsString('is not an implementation of', $message);
    }

    public static function noResolverForProvider(): array
    {
        return [
            // Empty possible list
            'empty possible list' => [
                'UserInterface',
                [],
                ''
            ],

            // Single possible interface
            'single possible interface' => [
                'UserInterface',
                ['PaymentInterface'],
                'PaymentInterface'
            ],

            // Multiple possible interfaces
            'multiple possible interfaces' => [
                'UserInterface',
                ['PaymentInterface', 'EmailInterface', 'CacheInterface'],
                'PaymentInterface, EmailInterface, CacheInterface'
            ],

            // Namespaced interfaces
            'namespaced interfaces' => [
                'App\\Repository\\UserRepositoryInterface',
                ['App\\Repository\\PaymentRepositoryInterface', 'App\\Service\\EmailServiceInterface'],
                'App\\Repository\\PaymentRepositoryInterface, App\\Service\\EmailServiceInterface'
            ],

            // PSR interfaces
            'psr interfaces' => [
                'Psr\\Log\\LoggerInterface',
                ['Psr\\Cache\\CacheItemPoolInterface', 'Psr\\Http\\Message\\RequestInterface'],
                'Psr\\Cache\\CacheItemPoolInterface, Psr\\Http\\Message\\RequestInterface'
            ],

            // Edge cases
            'interface with special chars' => [
                'Interface\\With"Special\'Chars',
                ['Normal\\Interface'],
                'Normal\\Interface'
            ],
            'possible with special chars' => [
                'NormalInterface',
                ['Interface\\With"Special\'Chars', 'Another\\Interface'],
                'Interface\\With"Special\'Chars, Another\\Interface'
            ],

            // Long lists
            'many possible interfaces' => [
                'TargetInterface',
                ['Interface1', 'Interface2', 'Interface3', 'Interface4', 'Interface5'],
                'Interface1, Interface2, Interface3, Interface4, Interface5'
            ],
        ];
    }

    public static function didNotResolveToStringProvider(): array
    {
        $resource = \fopen('php://memory', 'r');
        \fclose($resource);

        return [
            // Primitive types
            'integer value' => ['UserInterface', 123, 'int'],
            'float value' => ['UserInterface', 3.14, 'float'],
            'boolean true' => ['UserInterface', true, 'bool'],
            'boolean false' => ['UserInterface', false, 'bool'],
            'null value' => ['UserInterface', null, 'null'],
            'array value' => ['UserInterface', ['class' => 'User'], 'array'],

            // Objects
            'stdClass object' => ['UserInterface', new \stdClass(), 'stdClass'],
            'DateTime object' => ['UserInterface', new \DateTime(), 'DateTime'],
            'Exception object' => ['UserInterface', new \Exception(), 'Exception'],
            'closure' => ['UserInterface', fn() => 'User', 'Closure'],

            // Edge cases
            'empty array' => ['UserInterface', [], 'array'],
            'zero' => ['UserInterface', 0, 'int'],
            'empty string' => ['UserInterface', '', 'string'],
            'resource' => ['UserInterface', $resource, 'resource (closed)'],

            // Special numeric values
            'infinity' => ['UserInterface', \INF, 'float'],
            'negative infinity' => ['UserInterface', -\INF, 'float'],
            'not a number' => ['UserInterface', \NAN, 'float'],

            // Different interface names
            'psr interface' => ['Psr\\Log\\LoggerInterface', 123, 'int'],
            'namespaced interface' => ['App\\Repository\\UserRepositoryInterface', [], 'array'],
            'framework interface' => ['Symfony\\Component\\EventDispatcher\\EventDispatcherInterface', new \stdClass(), 'stdClass'],
        ];
    }

    public static function classIsNotAnImplementationOfProvider(): array
    {
        return [
            // Basic class and interface mismatch
            'simple mismatch' => ['UserInterface', 'PaymentClass'],
            'builtin class vs interface' => ['Iterator', 'stdClass'],
            'custom class vs psr interface' => ['Psr\\Log\\LoggerInterface', 'App\\User'],

            // Namespaced scenarios
            'namespaced interface and class' => [
                'App\\Repository\\UserRepositoryInterface',
                'App\\Model\\User'
            ],
            'deep namespace mismatch' => [
                'App\\Contracts\\Payment\\PaymentGatewayInterface',
                'App\\Services\\Email\\EmailService'
            ],

            // Framework scenarios
            'symfony interface mismatch' => [
                'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface',
                'App\\EventDispatcher'
            ],
            'laravel contract mismatch' => [
                'Illuminate\\Contracts\\Cache\\Repository',
                'App\\Cache\\FileCache'
            ],
            'doctrine interface mismatch' => [
                'Doctrine\\ORM\\EntityManagerInterface',
                'App\\EntityManager'
            ],

            // PSR interfaces
            'psr logger mismatch' => ['Psr\\Log\\LoggerInterface', 'Monolog\\Handler\\StreamHandler'],
            'psr cache mismatch' => ['Psr\\Cache\\CacheItemPoolInterface', 'Redis'],
            'psr http mismatch' => ['Psr\\Http\\Message\\RequestInterface', 'GuzzleHttp\\Client'],

            // Edge cases
            'empty interface name' => ['', 'SomeClass'],
            'empty class name' => ['SomeInterface', ''],
            'both empty' => ['', ''],
            'special characters in interface' => ['Interface\\With"Special\'Chars', 'NormalClass'],
            'special characters in class' => ['NormalInterface', 'Class\\With"Special\'Chars'],
            'both with special characters' => [
                'Interface\\With"Special\'Chars',
                'Class\\With"Different\'Chars'
            ],

            // Common real-world mismatches
            'repository interface with model' => ['UserRepositoryInterface', 'User'],
            'service interface with controller' => ['EmailServiceInterface', 'EmailController'],
            'gateway interface with service' => ['PaymentGatewayInterface', 'PaymentService'],
            'factory interface with builder' => ['UserFactoryInterface', 'UserBuilder'],

            // Built-in PHP interfaces
            'iterator with array object' => ['Iterator', 'ArrayObject'],
            'countable with collection' => ['Countable', 'Collection'],
            'serializable with entity' => ['Serializable', 'UserEntity'],
        ];
    }
}

