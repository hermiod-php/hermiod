<?php

declare(strict_types=1);

namespace Hermiod\Resource;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
interface PropertyBagInterface
{
    public function getProperties(): Property\CollectionInterface;
}