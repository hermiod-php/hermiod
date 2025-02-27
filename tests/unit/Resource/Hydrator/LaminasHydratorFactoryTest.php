<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Hydrator;

use Hermiod\Resource\Hydrator\LaminasHydratorFactory;
use Hermiod\Resource\Hydrator\LaminasHydrator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LaminasHydratorFactory::class)]
final class LaminasHydratorFactoryTest extends TestCase
{
    public function testCreateHydratorForClass(): void
    {
        $factory = new LaminasHydratorFactory();

        $hydrator = $factory->createHydratorForClass(\stdClass::class);

        $this->assertInstanceOf(
            LaminasHydrator::class,
            $hydrator,
            'createHydratorForClass() should return an instance of LaminasHydrator'
        );
    }

    public function testHydratorCaching(): void
    {
        $factory = new LaminasHydratorFactory();

        $hydrator1 = $factory->createHydratorForClass(\stdClass::class);
        $hydrator2 = $factory->createHydratorForClass(\stdClass::class);

        $this->assertSame(
            $hydrator1,
            $hydrator2,
            'createHydratorForClass() should return the same instance for the same class'
        );
    }
}
