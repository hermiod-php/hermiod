<?php

declare(strict_types=1);

namespace Hermiod\Tests\Integration\Fakes;

final class ArrayPropertiesFake
{
    private array $privateArrayWithoutDefaultNotNullable;
    protected array $protectedArrayWithoutDefaultNotNullable;
    public array $publicArrayWithoutDefaultNotNullable;

    private array $privateArrayWithDefaultNotNullable = ['default'];
    protected array $protectedArrayWithDefaultNotNullable = ['default'];
    public array $publicArrayWithDefaultNotNullable = ['default'];
    
    private ?array $privateArrayWithoutDefaultNullable;
    protected ?array $protectedArrayWithoutDefaultNullable;
    public ?array $publicArrayWithoutDefaultNullable;

    private ?array $privateArrayWithDefaultNullable = null;
    protected ?array $protectedArrayWithDefaultNullable = null;
    public ?array $publicArrayWithDefaultNullable = null;

    private $privateUntypedArrayWithDefaultNotNullable = ['default'];
    protected $protectedUntypedArrayWithDefaultNotNullable = ['default'];
    public $publicUntypedArrayWithDefaultNotNullable = ['default'];

    public function list(): array
    {
        return \get_object_vars($this);
    }
}
