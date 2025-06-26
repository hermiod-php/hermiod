<?php

declare(strict_types=1);

namespace Hermiod\Attribute;

interface ResourceInterface
{
    public function canAutoSerialize(): bool;

    public function getReflectionPropertyFilter(): int;
}