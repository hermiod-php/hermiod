<?php

declare(strict_types=1);

namespace Hermiod\Resource\Name;

final class ScreamingSnakeCase extends AbstractStrategy
{
    public function format(string $name): string
    {
        return \strtoupper(
            \implode(
                '_',
                $this->splitWords($name)
            )
        );
    }
}
