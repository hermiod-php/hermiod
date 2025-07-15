<?php

declare(strict_types=1);

namespace Hermiod\Resource\Name;

final class CachedNamingStrategy implements StrategyInterface
{
    /**
     * @var array<string, string>
     */
    private array $formatted = [];

    public static function wrap(StrategyInterface $strategy): StrategyInterface
    {
        if ($strategy instanceof self) {
            return $strategy;
        }

        return new self($strategy);
    }

    public function __construct(
        private readonly StrategyInterface $strategy,
    ) {}

    public function format(string $name): string
    {
        return $this->formatted[$name] ??= $this->strategy->format($name);
    }
}
