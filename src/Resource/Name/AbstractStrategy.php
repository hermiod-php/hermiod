<?php

declare(strict_types=1);

namespace Hermiod\Resource\Name;

abstract class AbstractStrategy implements StrategyInterface
{
    private const SPLIT_PATTERN = '/(?<=[a-z])(?=[A-Z])|_|-|\s+|(?<=[A-Z])(?=[A-Z][a-z])/';
    private const CLEAN_PATTERN = '/(\s|-|_)+/';

    /**
     * @return string[]
     */
    protected function splitWords(string $input): array
    {
        $input = \trim($input);

        $split = \preg_split(self::SPLIT_PATTERN, $input) ?: [$input];

        return \array_map('strtolower', $split);
    }

    public function normalise(string $name): string
    {
        $name = \trim($name);

        return \strtolower(
            (string)\preg_replace(self::CLEAN_PATTERN, '', $name)
        );
    }
}
