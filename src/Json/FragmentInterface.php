<?php

declare(strict_types=1);

namespace Hermiod\Json;

interface FragmentInterface
{
    public function has(string $key): bool;

    public function get(string $key): mixed;

    public function set(string $key, mixed $value): void;

    /**
     * @return array<mixed, mixed>
     */
    public function toArray(): array;
}
