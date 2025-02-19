<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Property;

interface PropertyInterface
{
    public function getConcrete(): ?string;

    public function getNameOverride(): ?string;
}