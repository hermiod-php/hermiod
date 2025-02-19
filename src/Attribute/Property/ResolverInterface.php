<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Property;

interface ResolverInterface
{
    public function getConcreteClass(string $interface, mixed $value): string;
}