<?php

declare(strict_types=1);

namespace JsonObjectify\Tests\Integration\Fakes;

final class IntegerPropertiesFake
{
    private int $privateIntegerWithoutDefaultNotNullable;
    protected int $protectedIntegerWithoutDefaultNotNullable;
    public int $publicIntegerWithoutDefaultNotNullable;

    private int $privateIntegerWithDefaultNotNullable = 42;
    protected int $protectedIntegerWithDefaultNotNullable = 42;
    public int $publicIntegerWithDefaultNotNullable = 42;
    
    private ?int $privateIntegerWithoutDefaultNullable;
    protected ?int $protectedIntegerWithoutDefaultNullable;
    public ?int $publicIntegerWithoutDefaultNullable;

    private ?int $privateIntegerWithDefaultNullable = null;
    protected ?int $protectedIntegerWithDefaultNullable = null;
    public ?int $publicIntegerWithDefaultNullable = null;

    private $privateUntypedIntegerWithDefaultNotNullable = 42;
    protected $protectedUntypedIntegerWithDefaultNotNullable = 42;
    public $publicUntypedIntegerWithDefaultNotNullable = 42;
}
