<?php

declare(strict_types=1);

namespace Hermiod\Resource\Name;

final class CamelCase extends AbstractStrategy
{
    public function format(string $name): string
    {
        return \lcfirst(
            \implode(
                '',
                \array_map(
                    static fn (string $word) => \ucfirst($word),
                    $this->splitWords($name)
                )
            )
        );
    }
}
