<?php

declare(strict_types=1);

namespace Hermiod\Json;

final class ArrayFragment implements FragmentInterface
{
    /**
     * @param array<mixed, mixed> $array
     */
    public function __construct(
        private array &$array,
    ) {}

    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->array);
    }

    public function get(string $key): mixed
    {
        return $this->array[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->array[$key] = $value;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return $this->array;
    }
}
