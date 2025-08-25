<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property\Resolver\Exception;

final class InterfaceNotFoundException extends \DomainException implements Exception
{
    public static function for(string $interface): self
    {
        return new self(
            \sprintf('Interface %s not found', $interface)
        );
    }
}
