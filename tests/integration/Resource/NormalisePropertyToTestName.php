<?php

declare(strict_types=1);

namespace Hermiod\Tests\Integration\Resource;

trait NormalisePropertyToTestName
{
    private static function normalise(string $name): string
    {
        return \ucfirst(
            \preg_replace('/(?<=[[:lower:]])(?=[[:upper:]])/u', ' ', $name)
        );
    }
}