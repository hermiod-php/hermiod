<?php

declare(strict_types=1);

namespace Hermiod\Tests\System\Fakes;

readonly class HasInterfacePropertyFake
{
    protected InterfaceImpls\TestInterface $object;

    public function getObject(): InterfaceImpls\TestInterface
    {
        return $this->object;
    }
}