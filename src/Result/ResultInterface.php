<?php

declare(strict_types=1);

namespace JsonObjectify\Result;

use JsonObjectify\Result\Error;

/**
 * @template T
 */
interface ResultInterface
{
    public function isValid(): bool;

    public function getErrors(): Error\CollectionInterface;

    /**
     * @return T
     *
     * @throws \Exception
     */
    public function toClassObject(): object;
}
