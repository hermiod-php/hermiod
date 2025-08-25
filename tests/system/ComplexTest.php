<?php

declare(strict_types=1);

namespace Hermiod\Tests\System;

use Hermiod\Converter;
use Hermiod\ConverterInterface;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;

#[Medium]
class ComplexTest extends TestCase
{
    public static ConverterInterface $converter;

    public static function setUpBeforeClass(): void
    {
        self::$converter = Converter::create();
    }

    public function testSuccessfulComplexCase(): void
    {
        $json = '
            {
                "datetime": "2021-09-08 11:00:52",
                "int": 985688466,
                "string": "test",
                "float": 562.02,
                "bool": true,
                "mixed": null,
                "stdClass": {"foo": "bar"},
                "object": {"foo": "bar"},
                "array": [1, 2, 3],
                "class": {
                    "nullableInt": null,
                    "nullableIntDefaultNull": 858585,
                    "nullableIntDefaultInt": 99,
                    "nonNullableInt": 0,
                    "nonNullableIntDefaultInt": 98,
                    "intGreaterThanTwo": 3,
                    "intLessThanTwo": 1,
                    "intGreaterThanOneLessThanThree": 2,
                    "intInList": 5,
                    "intGreaterThanOrEqualFive": 5,
                    "intLessThanOrEqualFive": 5
                }
            }
        ';

        $result = self::$converter->toClass(Fakes\ComplexFake::class, \json_decode($json));

        $this->assertInstanceOf(Fakes\ComplexFake::class, $result);

        $inner = $result->toArray();

        $this->assertInstanceOf(\DateTimeImmutable::class, $inner['datetime']);
        $this->assertSame('2021-09-08T11:00:52+00:00', $inner['datetime']->format(\DateTimeInterface::ATOM));

        $this->assertInstanceOf(\stdClass::class, $inner['stdClass']);
        $this->assertEquals((object)['foo' => 'bar'], $inner['stdClass']);

        $this->assertInstanceOf(\stdClass::class, $inner['object']);
        $this->assertEquals((object)['foo' => 'bar'], $inner['object']);

        $this->assertInstanceOf(Fakes\IntegerPropertiesFake::class, $inner['class']);
        $this->assertSame(null, $inner['class']->get('nullableInt'));
        $this->assertSame(858585, $inner['class']->get('nullableIntDefaultNull'));
        $this->assertSame(99, $inner['class']->get('nullableIntDefaultInt'));
        $this->assertSame(0, $inner['class']->get('nonNullableInt'));
        $this->assertSame(98, $inner['class']->get('nonNullableIntDefaultInt'));
        $this->assertSame(3, $inner['class']->get('intGreaterThanTwo'));
        $this->assertSame(1, $inner['class']->get('intLessThanTwo'));
        $this->assertSame(2, $inner['class']->get('intGreaterThanOneLessThanThree'));
        $this->assertSame(5, $inner['class']->get('intInList'));
        $this->assertSame(5, $inner['class']->get('intGreaterThanOrEqualFive'));
        $this->assertSame(5, $inner['class']->get('intLessThanOrEqualFive'));

        $this->assertSame(985688466, $inner['int']);
        $this->assertSame('test', $inner['string']);
        $this->assertSame(562.02, $inner['float']);
        $this->assertSame(true, $inner['bool']);
        $this->assertSame(null, $inner['mixed']);
        $this->assertSame([1, 2, 3], $inner['array']);
    }
}
