<?php

declare(strict_types=1);

namespace Hermiod\Resource;

use Hermiod\Exception\TooMuchRecursionException;
use Hermiod\Resource\Property\PrimitiveInterface;
use Hermiod\Attribute\ResourceInterface as Options;
use Hermiod\Json;

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

    /**
     * @param class-string<Type> $classname
     */
    public function __construct(
        private readonly string $classname,
        private readonly Property\FactoryInterface $factory,
        private readonly Name\StrategyInterface $naming,
        private readonly Options $options,
    )
    {
        if (!\class_exists($classname)) {
            throw new \InvalidArgumentException("Class $classname does not exist");
        }
    }

    public function getClassName(): string
    {
        return $this->classname;
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
            $this->fragment($json),
        );
    }

    private function recurse(
        Path\PathInterface $path,
        Property\CollectionInterface $properties,
        Property\Validation\ResultInterface $result,
        Json\FragmentInterface $json,
    ): Property\Validation\ResultInterface
    {
        if (++self::$depth > self::$maxRecursion) {
            throw TooMuchRecursionException::new(self::$maxRecursion);
        }

        foreach ($properties as $property) {
            $result = $this->process($path, $property, $result, $json);
        }

        --self::$depth;

        return $result;
    }

    public function canAutomaticallySerialise(): bool
    {
        return $this->options->canAutoSerialize();
    }

    private function process(
        Path\PathInterface $path,
        Property\PropertyInterface $property,
        Property\Validation\ResultInterface $result,
        Json\FragmentInterface $json,
    ): Property\Validation\ResultInterface
    {
        $key = $this->naming->format($property->getPropertyName());

        $next = $path->withObjectKey($key);

        if (!$json->has($key)) {
            if ($property->hasDefaultValue()) {
                $json->set($key, $property->getDefaultValue());

                return $result;
            }

            if ($property->isNullable()) {
                $json->set($key, null);

                return $result;
            }

            return $result->withErrors(
                \sprintf(
                    "%s is required",
                    $next->__toString(),
                )
            );
        }

        $data = $json->get($key);

        // Validate the value against the constraints
        $check = $property->checkValueAgainstConstraints($next, $data);

        if (!$check->isValid()) {
            return $result->withErrors(...$check->getValidationErrors());
        }

        if ($property instanceof PrimitiveInterface) {
            $json->set($key, $property->normalisePhpValue($data));

            return $result;
        }

        if (!\is_array($data) && !\is_object($data)) {
            return $result;
        }

        if ($property instanceof RuntimeResolverInterface) {
            $property = $property->getConcreteResource((array)$data);
        }

        if ($property instanceof ResourceInterface) {
            $result = $result->withHydrationStage(
                function (Hydrator\HydratorInterface $hydrator) use ($property, $json, $data, $key): void {
                    $json->set($key, $hydrator->hydrate($property->getClassName(), (array)$data));
                }
            );

            return $this->recurse(
                $next,
                $property->getProperties(),
                $result,
                $this->fragment($data)
            );
        }

        return $result;
    }

    /**
     * @param array<mixed, mixed>|object $json
     */
    private function fragment(array|object &$json): Json\FragmentInterface
    {
        return \is_object($json) ? new Json\ObjectFragment($json) : new Json\ArrayFragment($json);
    }
}
