<?php

declare(strict_types=1);

namespace JsonObjectify\Resource\Reflector;

use JsonObjectify\Resource\Path;
use JsonObjectify\Resource\Reflector\Property\Validation\Result;

final class Reflector implements ReflectorInterface
{
    private Property\CollectionInterface $properties;

    public function __construct(string $classname, Property\FactoryInterface $factory)
    {
        if (!\class_exists($classname)) {
            throw new \InvalidArgumentException("Class $classname does not exist");
        }

        $reflection = new \ReflectionClass($classname);

        $properties = [];

        foreach ($reflection->getProperties() as $property) {
            $properties[] = $factory->createPropertyFromReflectionProperty(
                $property
            );
        }

        $this->properties = new Property\Collection(...$properties);
    }

    public function getProperties(): Property\CollectionInterface
    {
        return $this->properties;
    }

    public function validate(object|array $json): Property\Validation\ResultInterface
    {
        $path = new Path\Root();
        $errors = [];

        $json = $this->toIterableMap($json);

        foreach ($json as $name => $value) {
            $path = $path->withObjectKey($name);

            if (!$this->properties->offsetExists($name)) {
                $errors[] = \sprintf(
                    'Property %s is not permitted',
                    $path->__toString(),
                );

                continue;
            }

            $property = $this->properties->offsetGet($name);
            $check = $property->checkValueAgainstConstraints($path, $value);

            if (!$check->isValid()) {
                $errors = \array_merge($errors, $check->getValidationErrors());
            }
        }

        return new Result(...$errors);
    }

    private function toIterableMap(object|array $json): array
    {
        if (\is_object($json) || (\is_array($json) && !\array_is_list($json))) {
            return (array) $json;
        }

        throw new \Exception();
    }
}
