<?php

declare(strict_types=1);

namespace Hermiod;

/**
 * @template Type of object
 */
interface ResourceManagerInterface
{
    /**
     * @param class-string<Type> $class
     *
     * @return TransposerInterface<Type>
     */
    public function getResource(string $class): TransposerInterface;
}
