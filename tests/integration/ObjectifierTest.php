<?php

declare(strict_types=1);

namespace Hermiod\Tests\Integration;

use Hermiod\Transposer;
use Hermiod\Tests\Integration\Fakes\StringPropertiesFake;
use PHPUnit\Framework\TestCase;

class ObjectifierTest extends TestCase
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

        $transposer = Transposer::create();

        $class = $transposer->parse($json, StringPropertiesFake::class)->instance();

        $this->assertInstanceOf(StringPropertiesFake::class, $class);
        $this->assertSame($json, $class->list());
    }
}
