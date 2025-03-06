<?php

declare(strict_types=1);

namespace Hermiod;

use Hermiod\Result\ResultInterface;

/**
 * @template Type of object
 */
interface TransposerInterface
{
    /**
     * @param string|object|array<mixed, mixed> $json
     *
     * @return ResultInterface<Type>
     */
    public function unserialize(string|object|array $json): ResultInterface;
}