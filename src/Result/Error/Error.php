<?php

declare(strict_types=1);

namespace Hermiod\Result\Error;

final readonly class Error implements ErrorInterface
{
    public function __construct(
        private string $message,
    ) {}

    public function getMessage(): string
    {
        return $this->message;
    }

    public function jsonSerialize(): string
    {
        return $this->getMessage();
    }
}
