<?php

declare(strict_types=1);

namespace Hermiod\Resource\Unserializer;

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
     *
     * @throws \Hermiod\Exception\Exception
     */
    public function unserialize(string|object|array $json): ResultInterface;
}