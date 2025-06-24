<?php

declare(strict_types=1);

namespace Hermiod\Tests\Integration\Resource;

use Hermiod\Resource\Constraint\CachedFactory as ConstraintFactory;
use Hermiod\Resource\Factory as ResourceFactory;
use Hermiod\Resource\ProxyCallbackFactory as LazyResourceFactory;
use Hermiod\Resource\Property\CollectionInterface;
use Hermiod\Resource\Property\Factory as PropertyFactory;
use Hermiod\Resource\Property\PropertyInterface;
use Hermiod\Resource\Resource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ResourceSuccessTest extends TestCase
{
    use ProvideArrayFakesPropertiesAndDefaults;
    use ProvideIntegerFakesPropertiesAndDefaults;
    use ProvideStringFakesPropertiesAndDefaults;
    use ProvideDateTimeFakesPropertiesAndDefaults;
    use ProvideDateTimeImmutableFakesPropertiesAndDefaults;

    #[DataProvider('provideStringFakesPropertiesAndDefaults')]
    #[DataProvider('provideIntegerFakesPropertiesAndDefaults')]
    #[DataProvider('provideArrayFakesPropertiesAndDefaults')]
    #[DataProvider('provideDateTimeFakesPropertiesAndDefaults')]
    #[DataProvider('provideDateTimeImmutableFakesPropertiesAndDefaults')]
    public function testCanParseTypedProperties(string $fake, string $name, string $class, bool $expectDefault, mixed $default = null): void
    {
        $factory = new ResourceFactory(
            $properties = new PropertyFactory(
                new ConstraintFactory(),
                new LazyResourceFactory(function () use (&$factory) {
                    return $factory;
                }),
            )
        );

        $resource = new Resource(
            $fake,
            $properties,
        );

        $properties = $resource->getProperties();

        $property = $this->getPropertyFromCollection($properties, $name, $fake);

        $this->assertInstanceOf(
            $class,
            $property,
            \sprintf('Property %s->%s was not parsed into a %s', $fake, $name, $class)
        );

        $this->assertSame($name, $property->getPropertyName());
        $this->assertSame($expectDefault, $property->hasDefaultValue());

        if ($expectDefault) {
            $this->assertSame($default, $property->getDefaultValue());
        }
    }

    private function getPropertyFromCollection(CollectionInterface $collection, string $name, string $fake): PropertyInterface
    {
        $this->assertArrayHasKey(
            $name,
            $collection,
            \sprintf('Property %s::%s not found', $fake, $name)
        );

        $property = $collection->offsetGet($name);

        $this->assertInstanceOf(
            PropertyInterface::class,
            $property,
            \sprintf('Property %s::%s was not parsed into a %s', $fake, $name, PropertyInterface::class)
        );

        return $property;
    }
}
