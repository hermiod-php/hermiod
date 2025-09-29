<?php

declare(strict_types=1);

namespace Hermiod\Traits;

trait JsonCompatibleTypeName
{
    private function getTypeName(mixed $value): string
    {
        if (\is_object($value)) {
            return 'object';
        }

        return \get_debug_type($value);
    }
}