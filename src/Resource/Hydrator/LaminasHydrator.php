<?php

declare(strict_types=1);

namespace Hermiod\Resource\Hydrator;

/**
 * @template Type of object
 */
final class LaminasHydrator implements HydratorInterface
{
    /**
     * @var \ReflectionClass<Type>
     */
    private \ReflectionClass $reflection;

    /**
     * @param class-string<Type> $className
     * @param \Laminas\Hydrator\HydratorInterface $hydrator
     */
    public function __construct(
        string $className,
        private \Laminas\Hydrator\HydratorInterface $hydrator,
    )
    {
        $this->reflection = new \ReflectionClass($className);
    }

    /**
     * @inheritDoc
     */
    public function hydrate(array|object $data): object
    {
        return $this->hydrator->hydrate(
            (array)$data,
            $this->reflection->newInstanceWithoutConstructor(),
        );
    }
}
