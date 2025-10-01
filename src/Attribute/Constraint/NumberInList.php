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
        /**
         * We intentionally use in_array here without strict checking
         * because we want to allow matching between ints and floats.
         * We have already narrowed the types in this class.
         */
        return \in_array($value, $this->values);
    }

    public function getMismatchExplanation(PathInterface $path, int|float $value): string
    {
        return \sprintf(
            '%s must be one of [%s] but %s given',
            $path->__toString(),
            \implode(', ', $this->values),
            $value,
        );
    }
}
