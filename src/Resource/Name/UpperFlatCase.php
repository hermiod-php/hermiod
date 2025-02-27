<?php

declare(strict_types=1);

namespace Hermiod\Resource\Name;

final class UpperFlatCase extends AbstractStrategy
{
    public function format(string $name): string
    {
        return \strtoupper(
            \implode(
                '',
                $this->splitWords($name)
            )
        );
    }
}
