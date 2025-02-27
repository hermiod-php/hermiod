<?php

declare(strict_types=1);

namespace Hermiod\Resource\Name;

final class CachedNamingStrategy implements StrategyInterface
{
    /**
     * @var array<string, string>
     */
    private array $formatted = [];

    /**
     * @var array<string, string>
     */
    private array $normalised = [];

    public function __construct(
        private readonly StrategyInterface $strategy,
    ) {}

    public function format(string $name): string
    {
        return $this->formatted[$name] ??= $this->strategy->format($name);
    }

    public function normalise(string $name): string
    {
        return $this->normalised[$name] ??= $this->strategy->normalise($name);
    }
}
