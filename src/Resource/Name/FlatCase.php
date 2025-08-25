<?php

declare(strict_types=1);

namespace Hermiod\Resource\Name;

final class FlatCase extends AbstractStrategy
{
    private const CLEAN_PATTERN = '/[\s\-_]+/i';

    public function format(string $name): string
    {
        return \strtolower(
            (string) \preg_replace(
                self::CLEAN_PATTERN,
                '',
                \trim($name),
            )
        );
    }
}
