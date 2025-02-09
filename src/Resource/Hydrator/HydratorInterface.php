<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Hydrator;

interface HydratorInterface
{
    public function hydrate(array|object $data): object;
}