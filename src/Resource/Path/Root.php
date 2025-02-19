<?php

declare(strict_types=1);

namespace Hermiod\Resource\Path;

final class Root implements PathInterface
{
    /**
     * @var string[]
     */
    private array $path = ['$'];

    public function __toString(): string
    {
        return \implode('.', $this->path);
    }

    public function withObjectKey(string $key): Root
    {
        $copy = clone $this;

        $copy->path[] = \trim($key, " \n\r\t\v\0.[]{}\"'");

        return $copy;
    }

    public function withArrayKey(int $key): Root
    {
        $copy = clone $this;

        $copy->path[] = \sprintf('[%s]', $key);

        return $copy;
    }
}