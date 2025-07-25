<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Resource\Path\PathInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class StringInList implements StringConstraintInterface
{
    /**
     * @var string[]
     */
    private array $values;

    public function __construct(string $value, string ...$values)
    {
        \array_unshift($values, $value);

        $this->values = $values;
    }

    public function valueMatchesConstraint(mixed $value): bool
    {
        return \in_array($value, $this->values, true);
    }

    public function getMismatchExplanation(PathInterface $path, string $value): string
    {
        return \sprintf(
            "%s must be one of [ '%s' ] but '%s' given",
            $path->__toString(),
            \implode("', '", $this->values),
            $value,
        );
    }
}
