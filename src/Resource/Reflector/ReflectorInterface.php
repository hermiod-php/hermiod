<?php

declare(strict_types=1);

namespace Hermiod\Resource\Reflector;

interface ReflectorInterface
{
    public function getProperties(): Property\CollectionInterface;

    /**
     * @param object|array<mixed> $json
     */
    public function validate(object|array $json): Property\Validation\ResultInterface;
}