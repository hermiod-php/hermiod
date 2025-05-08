<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Traits;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
trait ConvertToSameJsonValue
{
    public function normaliseJsonValue(mixed $value): mixed
    {
        return $value;
    }
}