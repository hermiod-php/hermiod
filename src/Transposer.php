<?php

declare(strict_types=1);

namespace Hermiod;

use Hermiod\Resource\Reflector;
use Hermiod\Resource\Hydrator;
use Hermiod\Result\Result;
use Hermiod\Result\ResultInterface;

final class Transposer implements TransposerInterface
{
    private const MAX_RECURSION = 1024;

    public static function create(): self
    {
        return new self(
            new Reflector\Factory(
                new Resource\Reflector\Property\Factory(
                    new Resource\Reflector\Constraint\CachedFactory()
                )
            ),
            new Hydrator\LaminasHydratorFactory()
        );
    }

    public function __construct(
        private Reflector\FactoryInterface $reflections,
        private Hydrator\FactoryInterface  $hydrators,
    ) {}

    /**
     * @inheritDoc
     */
    public function parse(string|object|array $json, string $class): ResultInterface
    {
        $reflector = $this->reflections->createReflectorForClass($class);
        $hydrator = $this->hydrators->createHydratorForClass($class);

        if (\is_string($json)) {
            $json = \json_decode($json, false, flags: JSON_THROW_ON_ERROR);

            if (!is_object($json)) {
                throw new \InvalidArgumentException('JSON string must be an object.');
            }
        }

        $this->transpose($reflector, $json);

        /**
         * @phpstan-ignore return.type
         */
        return new Result(
            $reflector,
            $hydrator,
            $json
        );
    }

    /**
     * @param Reflector\ReflectorInterface $reflector
     * @param object|array<mixed, mixed> $json
     * @param int $depth
     */
    private function transpose(Reflector\ReflectorInterface $reflector, object|array &$json, int $depth = 0): void
    {
        if ($depth > self::MAX_RECURSION) {
            throw new \OverflowException();
        }

        $properties = $reflector->getProperties();

        $isObject = \is_object($json);

        $list = $isObject ? \get_object_vars($json) : $json;

        foreach ($list as $key => $value) {
            if (!$properties->offsetExists($key)) {
                continue;
            }

            if ($property = $properties->offsetGet($key)) {
                $value = $property->convertToPhpValue($value);
            }

            if (\is_array($value) || \is_object($value)) {
                $this->transpose($reflector, $value, $depth + 1);
            }

            /**
             * PhpStan can't tell we're repeating a cached check without explicit \is_object()
             * @phpstan-ignore offsetAccess.nonOffsetAccessible
             */
            $isObject ? $json->{$key} = $value : $json[$key] = $value;
        }
    }
}
