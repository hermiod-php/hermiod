<?php

declare(strict_types=1);

namespace Hermiod\Result;

/**
 * @template TClass of object
 */
interface ResultInterface
{
    public function isValid(): bool;

    public function getErrors(): Error\CollectionInterface;

    /**
     * @return TClass
     *
     * @throws \Exception
     */
    public function getInstance(): object;
}
