<?php

declare(strict_types=1);

namespace Hermiod\Tests\System\Fakes;

final readonly class ComplexFake
{
    private \DateTimeInterface $datetime;

    protected int $int;

    public string $string;

    private IntegerPropertiesFake $class;

    private array $array;

    public float $float;

    private bool $bool;

    public mixed $mixed;

    public function __construct(
        protected \stdClass $stdClass,
        protected object $object,
    ) {}

    public function toArray(): array
    {
        return \get_object_vars($this);
    }
}
