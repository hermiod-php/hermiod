<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector\Property\Traits;

trait GetPropertyNameTrait
{
    private string $name;

    private function setName(string $name)
    {
        $this->name = $name;
    }

    public function getPropertyName(): string
    {
        return $this->name;
    }
}