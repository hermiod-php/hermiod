<?php

declare(strict_types=1);

namespace Hermiod\Resource;

use Hermiod\Resource\Path\PathInterface;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class Resource implements ResourceInterface
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

    public function validate(PathInterface $path, object|array $json): Property\Validation\ResultInterface
    {
        $errors = [];

        $json = $this->toIterableMap($json);

        foreach ($json as $name => $value) {
            $next = $path->withObjectKey($name);

            if (!$this->properties->offsetExists($name)) {
                $errors[] = \sprintf(
                    'Property %s is not permitted',
                    $next->__toString(),
                );

                continue;
            }

            $check = $this->properties->offsetGet($name)?->checkValueAgainstConstraints($next, $value);

            if ($check && !$check->isValid()) {
                $errors = \array_merge($errors, $check->getValidationErrors());
            }
        }

        return new Property\Validation\Result(...$errors);
    }

    /**
     * @param object|array<mixed, mixed> $json
     *
     * @return array<mixed, mixed>
     */
    private function toIterableMap(object|array $json): array
    {
        if (\is_object($json)) {
            return (array) $json;
        }

        return $json;
    }
}
