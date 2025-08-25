<?php

declare(strict_types=1);

namespace Hermiod\Resource\Name;

final class PascalSnakeCase extends AbstractStrategy
{
    public function format(string $name): string
    {
        return \implode(
            '_',
            \array_map(
                static fn (string $word) => \ucfirst($word),
                $this->splitWords($name)
            )
        );
    }
}
