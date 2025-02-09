<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Hydrator;

use JsonObjectify\Resource\Hydrator\Trait\RecursiveObjectToArrayTrait;

final class LaminasHydrator implements HydratorInterface
{
    use RecursiveObjectToArrayTrait;

    private \ReflectionClass $reflection;

    public function __construct(
        private string $className,
        private \Laminas\Hydrator\HydratorInterface $hydrator,
    )
    {
        $this->reflection = new \ReflectionClass($this->className);
    }

    public function hydrate(array|object $data): object
    {
        return $this->hydrator->hydrate(
            $this->objectsToArrays($data),
            $this->reflection->newInstanceWithoutConstructor(),
        );
    }
}
