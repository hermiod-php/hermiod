<?php

declare(strict_types=1);

namespace Hermiod\Resource\Hydrator;

interface HydratorInterface
{
    /**
     * @param array<mixed>|object $data
     */
    public function hydrate(array|object $data): object;

    public function getTargetClassname(): string;
}