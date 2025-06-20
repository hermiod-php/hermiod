<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Resource\Path\PathInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class NumberInList implements NumberConstraintInterface
{
    /**
     * @var float[]|int[]
     */
    private array $values;

    public function __construct(int|float $value, int|float ...$values)
    {
        \array_unshift($values, $value);

        $this->values = $values;
    }

    public function valueMatchesConstraint(int|float $value): bool
    {
        return \in_array($value, $this->values, true);
    }

    public function getMismatchExplanation(PathInterface $path, int|float $value): string
    {
        return \sprintf(
            '%s must be one of [ %s ] but %s given',
            $path->__toString(),
            \implode(', ', $this->values),
            $value,
        );
    }
}
