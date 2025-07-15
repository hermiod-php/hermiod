<?php

declare(strict_types=1);

namespace Hermiod\Resource\Name;

final class TrainCase extends AbstractStrategy
{
    public function format(string $name): string
    {
        return \implode(
            '-',
            \array_map(
                'ucfirst',
                $this->splitWords($name)
            )
        );
    }
}
