<?php

declare(strict_types=1);

namespace JsonObjectify\Result\Error;

use JsonObjectify\Resource\Path\PathInterface;

interface ErrorInterface extends \JsonSerializable
{
    public function getMessage(): string;

    public function jsonSerialize(): string;
}
