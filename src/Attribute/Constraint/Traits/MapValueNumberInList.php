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
            return \in_array($value, $this->values, true);
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