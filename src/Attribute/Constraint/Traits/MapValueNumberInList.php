<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Resource\Path\PathInterface;
use Hermiod\Traits\JsonCompatibleTypeName;

trait MapValueNumberInList
{
    use JsonCompatibleTypeName;

    /**
     * @var float[]|int[]
     */
    private array $values;

    public function __construct(int|float $value, int|float ...$values)
    {
        \array_unshift($values, $value);

        $this->values = $values;
    }

    public function mapValueMatchesConstraint(mixed $value): bool
    {
        if (\is_int($value) || \is_float($value)) {
            /**
             * We intentionally use in_array here without strict checking
             * because we want to allow matching between ints and floats.
             * We have already narrowed the types in the class.
             */
            return \in_array($value, $this->values);
        }

        return false;
    }

    public function getMismatchExplanation(PathInterface $path, mixed $value): string
    {
        return \sprintf(
            '%s must be one of [%s] but %s given',
            $path->__toString(),
            \implode(', ', $this->values),
            (\is_int($value) || \is_float($value)) ? $value : $this->getTypeName($value),
        );
    }
}