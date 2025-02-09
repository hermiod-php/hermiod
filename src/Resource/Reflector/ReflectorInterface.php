<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector;

interface ReflectorInterface
{
    public function getProperties(): Property\CollectionInterface;

    public function validate(object|array $json): Property\Validation\ResultInterface;
}