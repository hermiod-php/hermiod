<?php

declare(strict_types=1);

namespace Hermiod\Resource\Path;

interface PathInterface
{
    public function __toString(): string;

    public function withObjectKey(string $key): PathInterface;

    public function withArrayKey(int $key): PathInterface;
}
