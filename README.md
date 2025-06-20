# Hermiod

![version](https://img.shields.io/badge/version-1.0.0-green?labelColor=blue&style=flat)
![PHP](https://img.shields.io/badge/PHP-8.2.*_8.3.*_8.4.*-orange?labelColor=blue&style=flat)
![coverage](https://img.shields.io/badge/coverage-100%25-green?labelColor=blue&style=flat)

## About

Hermiod is a Object JSON Mapper which aims to fill the gap between JSON and PHP
using similar paradigms to Doctrine's Object Relationship Mapper.

PHP is a rich and expressive language with many features to express objects, properties, types, nullability, and defaults.

JSON is a similarly rich ruleset with objects, properties, and types. Tools like JsonSchema allow us to validate JSON
and infer nullability and defaults.

Hermiod bridge all of these things allowing developers to define JSON payload (for example; for HTTP request bodies) using PHP native types.
Hermiod can than validate the JSON against that PHP object, produce simple, serilaisable errors, and create
the fully hydrated class object graph from the supplied data.

## Basic usage

As a developer we should be free to use rich domain logic and the full range of PHP features in our application.

```php
namespace App;

final class Customer
{
    public array $accolades = [];
    
    protected ?float $value;
    
    private int $orderCount = 1;

    public function __construct(
        private string $name,
        protected \DateTimeImmutable $birthDate,
    ) {}
    
    public function getName(): name
    {
        return $this->name;
    }
}
```

The JSON version of this object will match properties by exact names and values which are
compatible with the PHP types. 

Any nullable properties or properties with defaults are treated as _optional_.

If defaults are provided, then they will be used when a JSON value is absent.

```php
{
    "name": "McKay",
    "birthDate": "1968-01-18 08:26:14+00:00:00",
    "accolades": [
        "PhD Physics",
        "PhD Mechanical Engineering",
    ]
}
```

The transposer will let us validate the JSON against the PHP object properties and types.
If validation fails, we can list the errors and even json-encode them directly to produce user-friendly
API responses.

```php
$manager = \Hermiod\ResourceManager::create();
$resource = $manager->getResource(\App\Customer::class);

$result = $resource->unserialize($json);

if (!$result->isValid()) {
    return \json_encode(
        $result->getErrors()
    );
}

$instance = $result->getInstance(); // App\Customer with all of the data set
```

If you want to covert your object back to JSON you can do so with PHP's `\JsonSerializable` interface.

## Advanced usage

### Out-of-the-box Supported Types

Hermiod supports multiple PHP native types, standard library classes, and some community types.
It will also support any loadable class declared in your codebase.

```php
namespace App;

final class OutOfTheBoxExamples
{
    private \App\OtherConcreteClass $sub; // Is created and hydrated just like this class
    
    private \Ramsey\UuidInterface $uuid; // If available will use UUID string constraint and hydrate
    
    private \DateTimeImmutable $dateTimeImmutable; // Supports ISO 8601 compatible string
    private \DateTime $dateTime;                   // Supports ISO 8601 compatible string
    private \DateTimeInterface $dateTimeInterface; // Creates \DateTimeImmutable by default
    private \stdClass $stdClass;                   // Any JSON object hashmap e.g. { "foo": 42 }
    
    private int $integer;     // Integer values only
    private float $float;     // Integer or float
    private string $string;   // Strings only
    private array $array;     // List-type array only [ "foo", "bar", 25, 42 ]
    private object $object;   // Any JSON object hashmap e.g. { "foo": 42 } converted to object with public properties
    private bool $boolean;    // Bool
    private mixed $mixed;     // Any of the above or null
    private $untyped;         // Implicitly the same as mixed
}
```

### Constraints

Many of the PHP native types can have additional value constraints added as attributes. You can find a full list
in `Hermiod\Attribute\Constraint\*`

Here are examples:

```php
namespace App;

use Hermiod\Attribute\Constraint as Assert;

final class OutOfTheBoxExamples
{
    #[Assert\NumberGreaterThanOrEquals(value: 1)]
    private int $integer; // Int must be greater than or equal to 1
    
    #[Assert\NumberGreaterThan(value: 0.01)]
    #[Assert\NumberLessThanOrEqual(value: 1)]
    private float $float; // Float greater than 0.01 and less than or equal to 1
    
    #[Assert\NumberInList(1, 5, 1.66)]
    private float $limitedNumber; // Must be one of 1, 2, or 1.66
    
    #[Assert\StringIsEmail()]
    private string $email; // Must be a valid email address
    
    #[Assert\StringIsUuid()]
    private string $uuid; // Must be a valid UUID
    
    #[Assert\StringInList('foo', 'bar', 'baz')]
    private string $limitedString; // Must be one of "foo", "bar", "baz"
    
    #[Assert\StringMatchesRegex(regex: '/^Foo.+/')]
    #[Assert\StringLengthGreaterThan(length: 8)]
    private string $startsWithFoo; // Must start with "Foo"
    
    #[Assert\StringLengthGreaterThanOrEqual(length: 1)]
    #[Assert\StringLengthLessThan(length: 3)]
    private string $oneOrTwoChars; // More that or equal to 1 char, fewer than 3 chars
    
    #[Assert\ArrayValueIsString()]
    #[Assert\MapValueStringMatchesRegex(regex: '/^Foo.+/')]
    private array $arrayOfStrings;   // Array must contain only strings which start with "Foo"
    
    #[Assert\ArrayValueIsInteger()]
    #[Assert\ArrayValueNumberLessThanOrEqual(value: -1)]
    private array $arrayOfIntegers; // Only integers less than -1
    
    #[Assert\ArrayValueIsFloat()]
    #[Assert\ArrayValueNumberLessThan(value: 100)]
    private array $arrayOfFloats; // Only floats less than 100
    
    #[Assert\ArrayValueIsArray()]
    private array $arrayOfArrays; // Values must be arrays, but nested validation is not supported
    
    #[Assert\ArrayValueIsObject()]
    private array $arrayOfObjects; // Values must be objects, but nested validation is not supported
    
    #[Assert\ObjectValueIsString()]
    #[Assert\ObjectValueStringMatchesRegex(regex: '/^Foo.+/')]
    private object $objectOfStrings;  // Any key with values starting with "Foo" e.g. { 6: "Food", "bar": "Foolish" }
    
    #[Assert\ObjectKeyStringMatchesRegex(regex: '/^Foo.+/')]
    private object $objectWithStringKeys;  // Any value with key starting with "Foo" e.g. { "Food": 56, "Foolish": "Hello" }
    
    private bool $boolean;    // Bool
    private mixed $mixed;     // Any of the above or null
    private $untyped;         // Implicitly the same as mixed
}
```


### Interfaces

When using interfaces for objects, Hermiod will need to know how to create some concrete class for the interface
at runtime.

An interface can be mapped to a concretion in the ResourceManager directly.

```php
$manager = \Hemiod\ResourceManager::create();

$manager = $manager->withInterfaceResolver(
    \App\SomeTypeInterface::class,
    fn () => \App\SomeConcrete::class,
);

$manager = $manager->withInterfaceResolver(
    \App\SomeOtherInterface::class,
    function (\Hemiod\Json\FramentInterface $data): string {
        return match ($data->get('$.something.some-key')) {
            'foo' => \App\SomeOtherFoo::class,
            'bar' => \App\SomeOtherBar::class,
            default => \App\SomeUnknownThing::class,
        };
    },
);
```
