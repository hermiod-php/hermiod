<?php

declare(strict_types=1);

namespace Hermiod\Resource\Hydrator;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 *
 * @template Type of object
 */
interface HydratorInterface
{
    /**
     * @param array<mixed>|object $data
     *
     * @return Type & object
     */
    public function hydrate(array|object $data): object;

    public function getTargetClassname(): string;
}