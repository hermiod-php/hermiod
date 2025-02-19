<?php

declare(strict_types=1);

namespace Hermiod\Resource\Hydrator;

interface HydratorInterface
{
    public function hydrate(array|object $data): object;
}