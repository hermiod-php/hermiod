<?php

declare(strict_types=1);

namespace Hermiod\Tests\System;

use Hermiod\Converter;
use Hermiod\ConverterInterface;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;

#[Medium]
class InterfaceTest extends TestCase
{
    public static ConverterInterface $converter;

    public static function setUpBeforeClass(): void
    {
        self::$converter = Converter::create();
    }

    public function testOneToOneMapping(): void
    {
        $json = '
            {
                "object": {
                    "id": 963
                }
            }
        ';

        $result = self::$converter
            ->addInterfaceResolver(Fakes\InterfaceImpls\TestInterface::class, Fakes\InterfaceImpls\TestImplWithInt::class)
            ->toClass(Fakes\HasInterfacePropertyFake::class, \json_decode($json));

        $this->assertInstanceOf(Fakes\HasInterfacePropertyFake::class, $result);
        $this->assertInstanceOf(Fakes\InterfaceImpls\TestImplWithInt::class, $result->getObject());
        $this->assertSame(963, $result->getObject()->id);
    }

    public function testManyToOneMapping(): void
    {
        $converter = self::$converter->addInterfaceResolver(
            Fakes\InterfaceImpls\TestInterface::class,
            function (array $fragment): string {
                $this->assertArrayHasKey('id', $fragment);

                return \is_int($fragment['id'])
                    ? Fakes\InterfaceImpls\TestImplWithInt::class
                    : Fakes\InterfaceImpls\TestImplWithString::class;
            },
        );

        $json = '
            {
                "object": {
                    "id": 963
                }
            }
        ';

        $result = $converter->toClass(Fakes\HasInterfacePropertyFake::class, $json);

        $this->assertInstanceOf(Fakes\HasInterfacePropertyFake::class, $result);
        $this->assertInstanceOf(Fakes\InterfaceImpls\TestImplWithInt::class, $result->getObject());
        $this->assertSame(963, $result->getObject()->id);

        $json = '
            {
                "object": {
                    "id": "foobar"
                }
            }
        ';

        $result = $converter->toClass(Fakes\HasInterfacePropertyFake::class, $json);

        $this->assertInstanceOf(Fakes\HasInterfacePropertyFake::class, $result);
        $this->assertInstanceOf(Fakes\InterfaceImpls\TestImplWithString::class, $result->getObject());
        $this->assertSame('foobar', $result->getObject()->id);
    }
}
