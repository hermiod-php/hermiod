<?php

declare(strict_types=1);

namespace Hermiod\Attribute;

interface PropertyInterface
{
    public function getNameOverride(): ?string;
}