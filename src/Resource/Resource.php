<?php

declare(strict_types=1);

namespace Hermiod\Resource;

use Hermiod\Exception\TooMuchRecursionException;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 *
 * @template Type of object
 * @template-implements ResourceInterface<Type>
 */
final class Resource implements ResourceInterface
{
    private const CLEAN_PATTERN = '/[\s\-_]+/i';

    private static int $maxRecursion = 128;
    private static int $depth = 0;

    private Property\CollectionInterface $properties;

    /**
     * @param class-string<Type> $classname
     */
    public function __construct(
        private readonly string $classname,
        private readonly Property\FactoryInterface $factory,
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

        foreach ($reflection->getProperties() as $property) {
            $properties[] = $this->factory->createPropertyFromReflectionProperty(
                $property
            );
        }

        return $this->properties = new Property\Collection(...$properties);
    }

    public function validateAndTranspose(Path\PathInterface $path, object|array &$json): Property\Validation\ResultInterface
    {
        $result = new Property\Validation\Result();

        return \is_array($json)
            ? $this->validateAndTransposeArray($path, $this->getProperties(), $result, $json)
            : $this->validateAndTransposeObject($path, $this->getProperties(), $result, $json);
    }

    private function validateAndTransposeObject(
        Path\PathInterface $path,
        Property\CollectionInterface $properties,
        Property\Validation\ResultInterface $result,
        object $json,
    ): Property\Validation\ResultInterface
    {
        if (++self::$depth > self::$maxRecursion) {
            throw TooMuchRecursionException::new(self::$maxRecursion);
        }

        $list = \get_object_vars($json);

        foreach ($list as $key => $data) {
            $normalised = $this->normalise($key);
            $property = $properties->offsetGet($normalised);
            $next = $path->withObjectKey($key);

            // TODO: Allow for optional key overloading
            if (!$property) {
                $result = $result->withError(
                    \sprintf(
                        'Property %s is not permitted',
                        $next->__toString(),
                    )
                );

                continue;
            }

            // Validate the value against the constraints
            $check = $property->checkValueAgainstConstraints($next, $data);

            if (!$check->isValid()) {
                $result = $result->withError(
                    \sprintf(
                        'Property %s is not permitted',
                        $next->__toString(),
                    )
                );

                continue;
            }

            // Transpose valid values if structure is still valid
            if ($result->isValid()) {
                unset($json->{$key});
                $json->{$property->getPropertyName()} = $property->normalisePhpValue($data);
            }
        }

        --self::$depth;

        return $result;
    }

    /**
     * @param array<mixed, mixed> $json
     */
    private function validateAndTransposeArray(
        Path\PathInterface $path,
        Property\CollectionInterface $properties,
        Property\Validation\ResultInterface $result,
        array &$json,
    ): Property\Validation\ResultInterface
    {
        if (++self::$depth > self::$maxRecursion) {
            throw TooMuchRecursionException::new(self::$maxRecursion);
        }

        $list = $json;

        foreach ($list as $key => $data) {
            $normalised = $this->normalise($key);
            $property = $properties->offsetGet($normalised);
            $next = $path->withObjectKey($key);

            // TODO: Allow for optional key overloading
            if (!$property) {
                $result = $result->withError(
                    \sprintf(
                        'Property %s is not permitted',
                        $next->__toString(),
                    )
                );

                continue;
            }

            // Validate the value against the constraints
            $check = $property->checkValueAgainstConstraints($next, $data);

            if (!$check->isValid()) {
                $result = $result->withError(
                    \sprintf(
                        'Property %s is not permitted',
                        $next->__toString(),
                    )
                );

                continue;
            }

            // Transpose valid values if structure is still valid
            if ($result->isValid()) {
                unset($json[$key]);
                $json[$property->getPropertyName()] = $property->normalisePhpValue($data);
            }
        }

        --self::$depth;

        return $result;
    }

    public function normalise(string $name): string
    {
        return \strtolower(
            (string) \preg_replace(
                self::CLEAN_PATTERN,
                '',
                \trim($name),
            )
        );
    }
}
