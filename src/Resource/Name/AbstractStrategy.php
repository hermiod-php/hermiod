<?php

declare(strict_types=1);

namespace Hermiod\Resource\Name;

abstract class AbstractStrategy implements StrategyInterface
{
    private const SPLIT_PATTERN = '/(?<=[a-z])(?=[A-Z])|_|-|\s+|(?<=[A-Z])(?=[A-Z][a-z])/';

    /**
     * @return string[]
     */
    protected function splitWords(string $input): array
    {
        $input = \trim($input);

        $split = \preg_split(self::SPLIT_PATTERN, $input) ?: [$input];

        return \array_map('strtolower', $split);
    }
}
