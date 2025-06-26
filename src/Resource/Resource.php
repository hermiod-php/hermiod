<?php

declare(strict_types=1);

namespace Hermiod\Resource;

use Hermiod\Exception\TooMuchRecursionException;
use Hermiod\Resource\Property\PrimitiveInterface;
use Hermiod\Attribute\ResourceInterface as Options;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 *
 * @template Type of object
 * @template-implements ResourceInterface<Type>
 */
final class Resource implements ResourceInterface
{
    private static int $maxRecursion = 128;
    private static int $depth = 0;

    private Property\CollectionInterface $properties;

    private int $filter;

    /**
     * @param class-string<Type> $classname
     */
    public function __construct(
        private readonly string $classname,
        private readonly Property\FactoryInterface $factory,
        private readonly Options $options,
    )
    {
        if (!\class_exists($classname)) {
            throw new \InvalidArgumentException("Class $classname does not exist");
        }
    }

    public function getProperties(): Property\CollectionInterface
    {
        if (isset($this->properties)) {
            return $this->properties;
        }

        $reflection = new \ReflectionClass($this->classname);

        $properties = [];

        foreach ($reflection->getProperties($this->options->getReflectionPropertyFilter()) as $property) {
            $properties[] = $this->factory->createPropertyFromReflectionProperty(
                $property
            );
        }

        return $this->properties = new Property\Collection(...$properties);
    }

    public function validateAndTranspose(Path\PathInterface $path, object|array &$json): Property\Validation\ResultInterface
    {
        return $this->recurse(
            $path,
            $this->getProperties(),
            new Property\Validation\Result(),
            $json,
        );
    }

    /**
     * @param object|array<mixed, mixed> $json
     */
    private function recurse(
        Path\PathInterface $path,
        Property\CollectionInterface $properties,
        Property\Validation\ResultInterface $result,
        object|array &$json,
    ): Property\Validation\ResultInterface
    {
        if (++self::$depth > self::$maxRecursion) {
            throw TooMuchRecursionException::new(self::$maxRecursion);
        }

        $list = \is_object($json) ? \get_object_vars($json) : $json;

        foreach ($list as $key => $data) {
            $property = $properties->offsetGet($key);
            $next = $path->withObjectKey($key);

            // TODO: Allow for optional key overloading
            if (!$property) {
                $result = $result->withErrors(
                    \sprintf(
                        "Property '%s' is not permitted",
                        $next->__toString(),
                    )
                );

                continue;
            }

            // Validate the value against the constraints
            $check = $property->checkValueAgainstConstraints($next, $data);

            if (!$check->isValid()) {
                $result = $result->withErrors(...$check->getValidationErrors());

                continue;
            }

            if ($property instanceof PrimitiveInterface) {
                if (\is_object($json)) {
                    unset($json->{$key});
                    $json->{$property->getPropertyName()} = $property->normalisePhpValue($data);

                    continue;
                }

                unset($json[$key]);
                $json[$property->getPropertyName()] = $property->normalisePhpValue($data);

                continue;
            }

            if (!\is_array($data) && !\is_object($data)) {
                continue;
            }

            if ($property instanceof ResourceInterface) {
                $result = $result->withErrors(
                    ...$property->validateAndTranspose($next, $data)->getValidationErrors()
                );
            }
        }

        --self::$depth;

        return $result;
    }

    public function canAutomaicallySerialise(): bool
    {
        return $this->options->canAutoSerialize();
    }
}
