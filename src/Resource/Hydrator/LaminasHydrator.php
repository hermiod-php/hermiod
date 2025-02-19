<?php

declare(strict_types=1);

namespace Hermiod\Resource\Hydrator;

final class LaminasHydrator implements HydratorInterface
{
    private \ReflectionClass $reflection;

    public function __construct(
        string $className,
        private \Laminas\Hydrator\HydratorInterface $hydrator,
    )
    {
        $this->reflection = new \ReflectionClass($className);
    }

    public function hydrate(array|object $data): object
    {
        return $this->hydrator->hydrate(
            (array)$data,
            $this->reflection->newInstanceWithoutConstructor(),
        );
    }
}
