<?php

declare(strict_types=1);

namespace Hermiod\Attribute;

interface PropertyInterface
{
    public function getConcrete(): ?string;

    public function getNameOverride(): ?string;
}