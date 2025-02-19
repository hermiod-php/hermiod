<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Property;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Resolver implements ResolverInterface
{
    public function __construct(
        private string $class,
    )
    {
        if (!\class_exists($this->class)) {
            throw new \Exception();
        }
    }

    public function getConcreteClass(string $interface, mixed $value): string
    {
        if (!\interface_exists($interface)) {
            throw new \Exception();
        }

        $interfaces = \class_implements($interface);

        if (false === $interfaces) {
            throw new \Exception();
        }

        if (!\in_array($interface, $interfaces, true)) {
            throw new \Exception();
        }

        return $this->class;
    }
}
