<?php

declare(strict_types=1);

namespace Hermiod\Resource\Hydrator;

/**
 * @template Type of object
 *
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class LaminasHydrator implements HydratorInterface
{
    /**
     * @var array<class-string, \ReflectionClass<Type>>
     */
    private array $reflections = [];

    /**
     * @param \Laminas\Hydrator\HydratorInterface $hydrator
     */
    public function __construct(
        private \Laminas\Hydrator\HydratorInterface $hydrator,
    ) {}

    /**
     * @param class-string<Type> $class
     * @param array<mixed>|object $data
     *
     * @return Type & object
     */
    public function hydrate(string $class, array|object $data): object
    {
        $reflection = $this->reflections[$class] ??= new \ReflectionClass($class);

        return $this->hydrator->hydrate(
            (array)$data,
            $reflection->newInstanceWithoutConstructor(),
        );
    }
}
