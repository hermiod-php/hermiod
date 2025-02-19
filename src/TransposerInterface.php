<?php

declare(strict_types=1);

namespace Hermiod;

use Hermiod\Result\ResultInterface;

interface TransposerInterface
{
    /**
     * @template Type of object
     *
     * @param string|object|array<mixed, mixed> $json
     * @param class-string<Type> $class
     *
     * @return ResultInterface<Type>
     */
    public function parse(string|object|array $json, string $class): ResultInterface;
}