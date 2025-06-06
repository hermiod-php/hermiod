<?php

declare(strict_types=1);

namespace Hermiod\Resource\Hydrator;

/**
 * @template Type of object
 * @template-implements HydratorInterface<Type>
 *
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
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
        private string $className,
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

    public function getTargetClassname(): string
    {
        return $this->className;
    }
}
