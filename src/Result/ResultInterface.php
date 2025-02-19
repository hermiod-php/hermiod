<?php

declare(strict_types=1);

namespace Hermiod\Result;

use Hermiod\Result\Error;

/**
 * @template Type
 */
interface ResultInterface
{
    public function isValid(): bool;

    public function getErrors(): Error\CollectionInterface;

    /**
     * @return Type
     *
     * @throws \Exception
     */
    public function instance(): object;
}
