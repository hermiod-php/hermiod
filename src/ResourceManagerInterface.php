<?php

declare(strict_types=1);

namespace Hermiod;

use Hermiod\Resource\UnserializerInterface;

/**
 * @template Type of object
 */
interface ResourceManagerInterface
{
    /**
     * @param class-string<Type> $class
     *
     * @return UnserializerInterface<Type>
     */
    public function getResource(string $class): UnserializerInterface;
}
