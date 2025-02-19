<?php

declare(strict_types=1);

namespace Hermiod\Attribute;

interface ResourceInterface
{
    public function autoIncludeProtectedProperties(): bool;

    public function autoIncludePrivateProperties(): bool;

    public function autoIncludePublicProperties(): bool;
}