<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Traits;

trait ConvertToSamePhpValueOrDefault
{
    abstract public function getDefaultValue(): mixed;

    abstract public function hasDefaultValue(): bool;

    public function normalisePhpValue(mixed $value): mixed
    {
        if (null === $value && $this->hasDefaultValue()) {
            return $this->getDefaultValue();
        }

        return $value;
    }

    public function normaliseJsonValue(mixed $value): mixed
    {
        return $value;
    }
}