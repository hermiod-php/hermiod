<?php

declare(strict_types=1);

namespace Hermiod\Resource;

use Hermiod\Resource\Path\PathInterface;

interface ResourceInterface
{
    public function getProperties(): Property\CollectionInterface;

    /**
     * @param PathInterface $path
     * @param object|array<mixed> $json
     */
    public function validate(PathInterface $path, object|array $json): Property\Validation\ResultInterface;
}