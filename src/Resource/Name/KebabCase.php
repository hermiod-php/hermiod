<?php

declare(strict_types=1);

namespace Hermiod\Resource\Name;

final class KebabCase extends AbstractStrategy
{
    public function format(string $name): string
    {
        return \strtolower(
            \implode(
                '-',
                $this->splitWords($name)
            )
        );
    }
}
