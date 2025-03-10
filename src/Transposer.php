<?php

declare(strict_types=1);

namespace Hermiod;

use Hermiod\Exception\JsonValueMustBeObjectException;
use Hermiod\Exception\TooMuchRecursionException;
use Hermiod\Resource\Hydrator;
use Hermiod\Resource\Name;
use Hermiod\Result\Result;
use Hermiod\Result\ResultInterface;

/**
 * @template Type of object
 * @template-implements TransposerInterface<Type>
 */
final readonly class Transposer implements TransposerInterface
{
    private const MAX_RECURSION = 128;

    /**
     * @param class-string<Type> $class
     */
    public function __construct(
        private Resource\FactoryInterface $reflections,
        private Hydrator\FactoryInterface $hydrators,
        private Name\StrategyInterface $naming,
        private string $class,
    ) {}

    /**
     * @param string|object|array<mixed, mixed> $json
     *
     * @return ResultInterface<Type>
     */
    public function unserialize(string|object|array $json): ResultInterface
    {
        $resource = $this->reflections->createResourceForClass($this->class);
        $hydrator = $this->hydrators->createHydratorForClass($this->class);

        if (\is_string($json)) {
            $json = \json_decode($json, false, flags: JSON_THROW_ON_ERROR);

            if (!\is_object($json)) {
                throw JsonValueMustBeObjectException::new($json);
            }
        }

        $this->transpose($resource, $json);

        /**
         * @phpstan-ignore return.type
         */
        return new Result(
            $resource,
            $hydrator,
            $json
        );
    }

    /**
     * @param Resource\ResourceInterface $resource
     * @param object|array<mixed, mixed> $json
     * @param int $depth
     */
    private function transpose(Resource\ResourceInterface $resource, object|array &$json, int $depth = 0): void
    {
        if ($depth > self::MAX_RECURSION) {
            throw TooMuchRecursionException::new(self::MAX_RECURSION);
        }

        $properties = $resource->getProperties();

        $isObject = \is_object($json);

        $list = $isObject ? \get_object_vars($json) : $json;

        foreach ($list as $key => $data) {
            $normalised = $this->naming->normalise($key);
            $property = $properties->offsetGet($normalised);

            if (!$property) {
                continue;
            }

            $value = $property->normalisePhpValue($data);

            if (\is_array($data) || \is_object($data)) {
                $this->transpose($resource, $data, $depth + 1);
            }

            if ($isObject) {
                unset($json->{$key});
                $json->{$property->getPropertyName()} = $value;

                continue;
            }

            /**
             * PhpStan can't tell we're repeating a cached check without explicit \is_object()
             * @phpstan-ignore offsetAccess.nonOffsetAccessible
             */
            unset($json[$key]);

            /** @phpstan-ignore offsetAccess.nonOffsetAccessible */
            $json[$property->getPropertyName()] = $value;
        }
    }
}
