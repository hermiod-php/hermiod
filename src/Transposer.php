<?php

declare(strict_types=1);

namespace Hermiod;

use Hermiod\Resource\Reflector;
use Hermiod\Resource\Hydrator;
use Hermiod\Result\Result;
use Hermiod\Result\ResultInterface;

final class Transposer
{
    private const MAX_RECURSION = 1024;

    private array $resolvers = [];

    public static function create(): self
    {
        return new self(
            new Reflector\Factory(
                new Resource\Reflector\Property\Factory()
            ),
            new Hydrator\LaminasHydratorFactory()
        );
    }

    public function __construct(
        private Reflector\FactoryInterface $reflections,
        private Hydrator\FactoryInterface  $hydrators,
    ) {}

    /**
     * @template Type
     *
     * @param string|object|array $json
     * @param class-string<Type> $class
     *
     * @return ResultInterface<Type>
     */
    public function parse(string|object|array $json, string $class): ResultInterface
    {
        $reflector = $this->reflections->createReflectorForClass($class);
        $hydrator = $this->hydrators->createHydratorForClass($class);

        if (\is_string($json)) {
            $json = \json_decode($json, false, flags: JSON_THROW_ON_ERROR);
        }

        $this->transpose($reflector, $json);

        return new Result(
            $reflector,
            $hydrator,
            $json
        );
    }

    public function withInterfaceResolver(string $interface, string $concrete): self
    {
        if (!\interface_exists($interface)) {
            throw new \Exception();
        }

        if (!\class_exists($concrete)) {
            throw new \Exception();
        }

        $copy = clone $this;

        $copy->resolvers[$interface] = $concrete;

        return $copy;
    }

    private function transpose(Reflector\ReflectorInterface $reflector, object|array &$json, int $depth = 0): void
    {
        if ($depth > self::MAX_RECURSION) {
            throw new \OverflowException();
        }

        $properties = $reflector->getProperties();

        foreach ($json as $key => $value) {
            if (!$properties->offsetExists($key)) {
                continue;
            }

            $value = $properties->offsetGet($key)->convertToPhpValue($value);

            if (\is_array($value) || \is_object($value)) {
                $this->transpose($reflector, $value, $depth + 1);
            }

            $json[$key] = $value;
        }
    }
}
