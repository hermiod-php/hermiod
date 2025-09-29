<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property\Resolver;

use Hermiod\Resource\Property\Resolver\Exception\InterfaceNotFoundException;
use Hermiod\Resource\Property\Resolver\Exception\ResolvedClassNameException;
use Hermiod\Resource\Property\Resolver\Resolver;
use Hermiod\Resource\Property\Resolver\ResolverInterface;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class ResolverTest extends TestCase
{
    public function testImplementsResolverInterface(): void
    {
        $resolver = new Resolver();

        $this->assertInstanceOf(ResolverInterface::class, $resolver);
    }

    public function testAddingValidResolverTypeAsCallable(): void
    {
        $resolver = new Resolver();

        $sample = new class() implements \Stringable {
            public function __toString(): string
            {
                return 'sample';
            }
        };

        $json = [
            'type' => 'string',
            'format' => 'date-time',
        ];

        $resolver->addResolver(\Stringable::class, function (array $fragment) use ($sample, $json): string {
            $this->assertSame(
                $json,
                $fragment,
                'The fragment passed to the resolver should match the expected JSON fragment.'
            );

            return $sample::class;
        });

        $result = $resolver->resolve(\Stringable::class, $json);

        $this->assertSame(
            $sample::class,
            $result,
            'The resolved class should match the expected class name.'
        );
    }

    public function testAddingInvalidResolverTypeAsCallable(): void
    {
        $resolver = new Resolver();

        $resolver->addResolver(\Stringable::class, function (array $fragment): string {
            return \stdClass::class;
        });

        $this->expectException(ResolvedClassNameException::class);
        $this->expectExceptionMessageMatches('/is not an implementation of the interface/');

        $resolver->resolve(\Stringable::class, []);
    }

    #[TestWith([true], 'bool true')]
    #[TestWith([false], 'bool false')]
    #[TestWith([[]], 'array')]
    #[TestWith([new \stdClass()], 'object')]
    #[TestWith([1], 'int')]
    #[TestWith([1.1], 'float')]
    #[TestWith([null], 'null')]
    public function testAddingCallableWithInvalidReturn(mixed $type): void
    {
        $resolver = new Resolver();

        $resolver->addResolver(\Stringable::class, function () use ($type): mixed {
            return $type;
        });

        $this->expectException(ResolvedClassNameException::class);
        $this->expectExceptionMessageMatches('/did not resolve to a class string/');

        $resolver->resolve(\Stringable::class, []);
    }

    public function testAddingValidResolverTypeAsString(): void
    {
        $resolver = new Resolver();

        $sample = new class() implements \Stringable {
            public function __toString(): string
            {
                return 'sample';
            }
        };

        $resolver->addResolver(\Stringable::class, $sample::class);

        $result = $resolver->resolve(\Stringable::class, []);

        $this->assertSame(
            $sample::class,
            $result,
            'The resolved class should match the expected class name.'
        );
    }

    public function testMissingResolver(): void
    {
        $resolver = new Resolver();

        $this->expectException(ResolvedClassNameException::class);
        $this->expectExceptionMessageMatches('/No resolver has been mapped for/');

        $resolver->resolve(\Stringable::class, []);
    }

    #[TestWith([\stdClass::class], 'concrete class')]
    #[TestWith(['random_nonsense7573735'], 'random string')]
    public function testAddingResolverForNonInterface(string $interface): void
    {
        $resolver = new Resolver();

        $this->expectException(InterfaceNotFoundException::class);

        $resolver->addResolver($interface, \stdClass::class);
    }

    #[TestWith([\stdClass::class], 'concrete class')]
    #[TestWith(['random_nonsense7573735'], 'random string')]
    public function testAddingResolverForIncompatibleClass(string $class): void
    {
        $resolver = new Resolver();

        $this->expectException(ResolvedClassNameException::class);

        $resolver->addResolver(\Stringable::class, $class);
    }
}
