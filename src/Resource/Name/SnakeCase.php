<?php

declare(strict_types=1);

namespace Hermiod\Resource\Name;

final class SnakeCase extends AbstractStrategy
{
    public function format(string $name): string
    {
        return \strtolower(
            \implode(
                '_',
                $this->splitWords($name)
            )
        );
    }
}
