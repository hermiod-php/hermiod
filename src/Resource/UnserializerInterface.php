<?php

declare(strict_types=1);

namespace Hermiod\Resource;

use Hermiod\Result\ResultInterface;

/**
 * @template Type of object
 */
interface UnserializerInterface
{
    /**
     * @param string|object|array<mixed, mixed> $json
     *
     * @return ResultInterface<Type>
     */
    public function unserialize(string|object|array $json): ResultInterface;
}