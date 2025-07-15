<?php

declare(strict_types=1);

namespace Hermiod\Tests\System;

use Hermiod\Exception\TooMuchRecursionException;
use Hermiod\Resource\Resource;
use Hermiod\Converter;
use Hermiod\Tests\System\Fakes\RecursiveTestClass;
use Hermiod\Tests\System\Fakes\StringPropertiesFake;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;

#[Medium]
class TransposeTest extends TestCase
{
    public function testSuccessfulHydrate(): void
    {
        $json = [
            'privateStringWithoutDefaultNotNullable' => 'A long time ago',
            'protectedStringWithoutDefaultNotNullable' => 'in a galaxy far, far away',
            'publicStringWithoutDefaultNotNullable' => 'It is a period of civil war',

            'privateStringWithDefaultNotNullable' => 'Rebel spaceships',
            'protectedStringWithDefaultNotNullable' => 'striking from a hidden base',
            'publicStringWithDefaultNotNullable' => 'have won their first victory',

            'privateStringWithoutDefaultNullable' => 'against the evil Galactic Empire',
            'protectedStringWithoutDefaultNullable' => 'During the battle',
            'publicStringWithoutDefaultNullable' => 'Rebel spies managed',

            'privateStringWithDefaultNullable' => 'to steal secret plans',
            'protectedStringWithDefaultNullable' => 'to the Empire\'s ultimate weapon',
            'publicStringWithDefaultNullable' => 'the DEATH STAR',

            'privateUntypedStringWithDefaultNotNullable' => 'an armored space station',
            'protectedUntypedStringWithDefaultNotNullable' => 'with enough power',
            'publicUntypedStringWithDefaultNotNullable' => 'to destroy an entire planet',

            'stringWithAttrRegex' => 'boofoohoo',
            'stringWithAttrUuid' => '6fe5a6ef-64ef-4306-89a9-b46664f88830',
            'stringWithAttrUuidAndRegex' => '9223825f-7733-4095-f000-1499cdaef497',
            'stringWithAttrEmail' => 'bar@bar.com',
            'stringWithAttrEmailAndRegex' => 'bar@foo.com',
        ];

        $transposer = Converter::create();

        $class = $transposer->toClass(StringPropertiesFake::class, $json);

        $this->assertInstanceOf(StringPropertiesFake::class, $class);
        $this->assertSame($json, $class->list());
    }

    public function testUnsuccessfulHydrate(): void
    {
        $json = [
            'privateStringWithoutDefaultNotNullable' => null,
            'protectedStringWithoutDefaultNotNullable' => 20,
            'publicStringWithoutDefaultNotNullable' => [],

            'privateStringWithDefaultNotNullable' => (object)[],
            'protectedStringWithDefaultNotNullable' => true,
            'publicStringWithDefaultNotNullable' => -58.25,

            'privateStringWithoutDefaultNullable' => false,
            'protectedStringWithoutDefaultNullable' => 0,
            'publicStringWithoutDefaultNullable' => ['Rebel spies managed'],

            'privateStringWithDefaultNullable' => 0,
            'protectedStringWithDefaultNullable' => false,
            'publicStringWithDefaultNullable' => 90,

            'privateUntypedStringWithDefaultNotNullable' => false,
            'protectedUntypedStringWithDefaultNotNullable' => [],
            'publicUntypedStringWithDefaultNotNullable' => 20,

            'stringWithAttrRegex' => ' ',
            'stringWithAttrUuid' => 'not-a-uuid',
            'stringWithAttrUuidAndRegex' => 'not-a-uuid-either',
            'stringWithAttrEmail' => 'bar@bar',
            'stringWithAttrEmailAndRegex' => 'holy@mackrel...foo',
        ];

        $transposer = Converter::create();

        $result = $transposer->tryToClass(StringPropertiesFake::class, $json);

        $this->assertFalse($result->isValid());

        $errors = \array_map(
            fn ($error) => $error->getMessage(),
            \iterator_to_array($result->getErrors()),
        );

        $this->assertArrayContains('$.privateStringWithoutDefaultNotNullable must be a string but null given', $errors);
        $this->assertArrayContains('$.protectedStringWithoutDefaultNotNullable must be a string but int given', $errors);
        $this->assertArrayContains('$.publicStringWithoutDefaultNotNullable must be a string but array given', $errors);

        $this->assertArrayContains('$.privateStringWithDefaultNotNullable must be a string but stdClass given', $errors);
        $this->assertArrayContains('$.protectedStringWithDefaultNotNullable must be a string but bool given', $errors);
        $this->assertArrayContains('$.publicStringWithDefaultNotNullable must be a string but float given', $errors);

        $this->assertArrayContains('$.privateStringWithoutDefaultNullable must be a string but bool given', $errors);
        $this->assertArrayContains('$.protectedStringWithoutDefaultNullable must be a string but int given', $errors);
        $this->assertArrayContains('$.publicStringWithoutDefaultNullable must be a string but array given', $errors);

        $this->assertArrayContains('$.privateStringWithDefaultNullable must be a string but int given', $errors);
        $this->assertArrayContains('$.protectedStringWithDefaultNullable must be a string but bool given', $errors);
        $this->assertArrayContains('$.publicStringWithDefaultNullable must be a string but int given', $errors);

        $this->assertArrayContains("$.stringWithAttrRegex must must match regex '/foo/' but ' ' given", $errors);
        $this->assertArrayContains("$.stringWithAttrUuid must be a UUID string but 'not-a-uuid' given", $errors);
        $this->assertArrayContains("$.stringWithAttrUuidAndRegex must be a UUID string but 'not-a-uuid-either' given", $errors);
        $this->assertArrayContains("$.stringWithAttrEmail must be an email address but 'bar@bar' given", $errors);
        $this->assertArrayContains("$.stringWithAttrEmailAndRegex must be an email address but 'holy@mackrel...foo' given", $errors);

        $this->assertCount(17, $errors);
    }

    public function testRecursionDepthLimitThrows(): void
    {
        $generate = function (array &$inital, int $depth) use (&$generate): array
        {
            if ($depth >= 129) {
                return $inital;
            }

            $array = [];

            $inital['object'] = $generate($array, $depth + 1 );

            return $inital;
        };

        $reflection = new \ReflectionClass(Resource::class);
        $reflection->setStaticPropertyValue('maxRecursion', 25);

        $transposer = Converter::create();

        $this->expectException(TooMuchRecursionException::class);
        $this->expectExceptionMessage('Exceeded the maximum object depth of 25 nested objects');

        $json = [];

        $transposer->tryToClass(RecursiveTestClass::class, $generate($json, 0))->getInstance();
    }

    private function assertArrayContains(string $needle, array $haystack): void
    {
        $this->assertTrue(
            \in_array($needle, $haystack, true),
            \sprintf(
                "String '%s' was not found in array\n%s",
                $needle,
                \json_encode($haystack, JSON_PRETTY_PRINT)
            ),
        );
    }
}
