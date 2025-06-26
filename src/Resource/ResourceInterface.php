<?php

declare(strict_types=1);

namespace Hermiod\Resource;

use Hermiod\Resource\Path\PathInterface;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 *
 * @template Type of object
 */
interface ResourceInterface
{
    public function getProperties(): Property\CollectionInterface;

    public function canAutomaicallySerialise(): bool;

    /**
     * @param PathInterface $path
     * @param object|array<mixed> $json
     */
    public function validateAndTranspose(PathInterface $path, object|array &$json): Property\Validation\ResultInterface;
}