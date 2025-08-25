<?php

declare(strict_types=1);

namespace Hermiod\Json;

final class ObjectFragment implements FragmentInterface
{
    public function __construct(
        private object $object,
    ) {}

    public function has(string $key): bool
    {
        return \property_exists($this->object, $key);
    }

    public function get(string $key): mixed
    {
        return $this->object->{$key} ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->object->{$key} = $value;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return \get_object_vars($this->object);
    }
}
