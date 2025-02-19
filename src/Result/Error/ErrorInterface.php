<?php

declare(strict_types=1);

namespace Hermiod\Result\Error;

use Hermiod\Resource\Path\PathInterface;

interface ErrorInterface extends \JsonSerializable
{
    public function getMessage(): string;

    public function jsonSerialize(): string;
}
