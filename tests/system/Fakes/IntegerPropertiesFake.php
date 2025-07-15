<?php

declare(strict_types=1);

namespace Hermiod\Tests\System\Fakes;

use Hermiod\Attribute\Constraint as Assert;

final class IntegerPropertiesFake
{
    private ?int $nullableInt;
    private ?int $nullableIntDefaultNull = null;
    private ?int $nullableIntDefaultInt = 99;

    protected int $nonNullableInt;
    protected int $nonNullableIntDefaultInt = 97;

    #[Assert\NumberGreaterThan(2)]
    public int $intGreaterThanTwo;

    #[Assert\NumberLessThan(2)]
    public int $intLessThanTwo;

    #[Assert\NumberGreaterThan(1)]
    #[Assert\NumberLessThan(3)]
    public int $intGreaterThanOneLessThanThree;

    #[Assert\NumberInList(5, 6, 7)]
    public int $intInList;

    #[Assert\NumberGreaterThanOrEqual(5)]
    public int $intGreaterThanOrEqualFive;

    #[Assert\NumberLessThanOrEqual(5)]
    public int $intLessThanOrEqualFive;

    public function get(string $name): ?int
    {
        return $this->{$name};
    }
}
