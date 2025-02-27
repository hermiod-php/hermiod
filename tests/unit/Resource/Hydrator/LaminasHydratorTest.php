<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Hydrator;

use Hermiod\Resource\Hydrator\LaminasHydrator;
use Hermiod\Resource\Hydrator\HydratorInterface;
use Laminas\Hydrator\HydratorInterface as LaminasHydratorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LaminasHydrator::class)]
final class LaminasHydratorTest extends TestCase
{
    public function testImplementsHydratorInterface(): void
    {
        $hydrator = new LaminasHydrator(\stdClass::class, $this->mockLaminasHydrator());

        $this->assertInstanceOf(
            HydratorInterface::class,
            $hydrator,
            'LaminasHydrator should implement HydratorInterface'
        );
    }

    public function testGetTargetClassname(): void
    {
        $className = \stdClass::class;
        $hydrator = new LaminasHydrator($className, $this->mockLaminasHydrator());

        $this->assertSame(
            $className,
            $hydrator->getTargetClassname(),
            'getTargetClassname() should return the provided class name'
        );
    }

    public function testHydrateCallsLaminasHydrator(): void
    {
        $data = ['key' => 'value'];
        $object = new \stdClass();

        $laminasHydrator = $this->mockLaminasHydrator();
        $laminasHydrator->expects($this->once())
            ->method('hydrate')
            ->with($data, $this->isInstanceOf(\stdClass::class))
            ->willReturn($object);

        $hydrator = new LaminasHydrator(\stdClass::class, $laminasHydrator);

        $this->assertSame(
            $object,
            $hydrator->hydrate($data),
            'hydrate() should call Laminas Hydrator and return the hydrated object'
        );
    }

    private function mockLaminasHydrator(): LaminasHydratorInterface & \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(LaminasHydratorInterface::class);
    }
}
