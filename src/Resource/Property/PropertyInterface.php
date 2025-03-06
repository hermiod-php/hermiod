<?php

declare(strict_types=1);

namespace Hermiod\Resource\Property;

use Hermiod\Resource\Path\PathInterface;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
interface PropertyInterface
{
    public function getPropertyName(): string;

    public function getDefaultValue(): mixed;

    public function hasDefaultValue(): bool;

    public function checkValueAgainstConstraints(PathInterface $path, mixed $value): Validation\ResultInterface;

    public function normalisePhpValue(mixed $value): mixed;

    public function normaliseJsonValue(mixed $value): mixed;
}
