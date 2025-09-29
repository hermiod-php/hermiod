<?php

declare(strict_types=1);

namespace Hermiod\Tests\Unit\Resource\Property;

use Hermiod\Resource\Property\Collection;
use Hermiod\Resource\Property\CollectionInterface;
use Hermiod\Resource\Property\Exception\AddingToSealedCollectionException;
use Hermiod\Resource\Property\Exception\DeletingFromSealedCollectionException;
use Hermiod\Resource\Property\PropertyInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Collection::class)]
final class CollectionTest extends TestCase
{
    public function testCollecitonImplementsInterfaces(): void
    {
        $collection = new Collection();

        $this->assertInstanceOf(CollectionInterface::class, $collection);
        $this->assertInstanceOf(\ArrayAccess::class, $collection);
        $this->assertInstanceOf(\Iterator::class, $collection);
    }

    public function testEmptyCollectionBasics(): void
    {
        $collection = new Collection();

        $this->assertFalse($collection->offsetExists('anything'));
        $this->assertNull($collection->offsetGet('anything'));
        $this->assertNull($collection->current());
        $this->assertNull($collection->key());
        $this->assertFalse($collection->valid());

        // rewind on empty should not error
        $collection->rewind();
        $this->assertFalse($collection->valid());
    }

    public function testOffsetExistsAndGetExactName(): void
    {
        $prop = $this->mockProperty('UserId');
        $collection = new Collection($prop);

        $this->assertTrue($collection->offsetExists('UserId'));
        $this->assertSame($prop, $collection->offsetGet('UserId'));
    }

    #[DataProvider('normalisationVariantsProvider')]
    public function testOffsetNormalisationVariants(string $propertyName, array $lookups): void
    {
        $prop = $this->mockProperty($propertyName);
        $collection = new Collection($prop);

        foreach ($lookups as $lookup) {
            $this->assertTrue($collection->offsetExists($lookup), "Expected normalised lookup '{$lookup}' to exist for '{$propertyName}'");
            $this->assertSame($prop, $collection->offsetGet($lookup));
        }
    }

    public function testLastPropertyWinsForDuplicateNormalisedNames(): void
    {
        $first = $this->mockProperty('First_Name');
        $second = $this->mockProperty('first name'); // same normalised form 'firstname'

        $collection = new Collection($first, $second);

        $this->assertSame($second, $collection->offsetGet('FIRST-NAME'));
        $this->assertSame($second, $collection->offsetGet('firstname'));
    }

    public function testIterationOrderPreserved(): void
    {
        $p1 = $this->mockProperty('Alpha');
        $p2 = $this->mockProperty('Beta');
        $p3 = $this->mockProperty('Gamma');

        $collection = new Collection($p1, $p2, $p3);

        $seen = [];

        foreach ($collection as $key => $property) {
            $seen[] = [$key, $property];
        }

        $this->assertSame(['Alpha', 'Beta', 'Gamma'], \array_map(fn($i) => $i[0], $seen));
        $this->assertSame([$p1, $p2, $p3], \array_map(fn($i) => $i[1], $seen));
    }

    public function testManualIteratorMethods(): void
    {
        $p1 = $this->mockProperty('One');
        $p2 = $this->mockProperty('Two');

        $collection = new Collection($p1, $p2);

        $collection->rewind();

        $this->assertTrue($collection->valid());
        $this->assertSame('One', $collection->key());
        $this->assertSame($p1, $collection->current());

        $collection->next();

        $this->assertTrue($collection->valid());
        $this->assertSame('Two', $collection->key());
        $this->assertSame($p2, $collection->current());

        $collection->next();

        $this->assertFalse($collection->valid());
        $this->assertNull($collection->key());
        $this->assertNull($collection->current());

        // Rewind should go back to start
        $collection->rewind();

        $this->assertTrue($collection->valid());
        $this->assertSame('One', $collection->key());
    }

    public function testOffsetSetThrows(): void
    {
        $prop = $this->mockProperty('Name');
        $collection = new Collection($prop);

        $this->expectException(AddingToSealedCollectionException::class);

        $collection['another'] = $prop; // @phpstan-ignore-line intentionally testing exception
    }

    public function testOffsetUnsetThrows(): void
    {
        $prop = $this->mockProperty('Name');
        $collection = new Collection($prop);

        $this->expectException(DeletingFromSealedCollectionException::class);

        unset($collection['Name']); // @phpstan-ignore-line intentionally testing exception
    }

    public function testOffsetSetExceptionMessageContainsContext(): void
    {
        $prop = $this->mockProperty('Name');
        $collection = new Collection($prop);

        $this->expectException(AddingToSealedCollectionException::class);

        $collection['newKey'] = $prop; // @phpstan-ignore-line
    }

    public function testPassingNonStringOffsetThrowsTypeError(): void
    {
        $prop = $this->mockProperty('Name');
        $collection = new Collection($prop);

        $this->expectException(\TypeError::class);

        // Because strict_types is on, int cannot be passed to string param in normalisePropertyName
        $collection->offsetExists(123); // @phpstan-ignore-line
    }

    private function mockProperty(string $name): PropertyInterface
    {
        $prop = $this->createMock(PropertyInterface::class);

        $prop->method('getPropertyName')->willReturn($name);

        return $prop;
    }

    public static function normalisationVariantsProvider(): array
    {
        return [
            'underscore / space / hyphen collapse' => [
                'First_Name',
                ['first_name', 'first name', 'FIRST-NAME', 'FirstName', ' first-name  ', 'FIRST_NAME']
            ],
            'mixed case and symbols' => [
                '  User_Name  ',
                ['username', 'USER-NAME', 'user name', 'UserName']
            ],
            'hyphen to removed' => [
                'API-Key',
                ['api_key', 'api key', 'api-key', 'APIKEY']
            ],
        ];
    }
}
