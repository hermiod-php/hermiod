<?php

declare(strict_types=1);

namespace Hermiod\Resource\Name;

final class FlatCase extends AbstractStrategy
{
    public function format(string $name): string
    {
        /**
         * FlatCase happens to be our normalised format as well
         */
        return $this->normalise($name);
    }
}
