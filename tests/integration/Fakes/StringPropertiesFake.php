<?php

declare(strict_types=1);

namespace JsonObjectify\Tests\Integration\Fakes;

use JsonObjectify\Resource\Attribute\Constraint\StringIsEmail;
use JsonObjectify\Resource\Attribute\Constraint\StringIsUuid;
use JsonObjectify\Resource\Attribute\Constraint\StringMatchesExpression;

final class StringPropertiesFake
{
    private string $privateStringWithoutDefaultNotNullable;
    protected string $protectedStringWithoutDefaultNotNullable;
    public string $publicStringWithoutDefaultNotNullable;

    private string $privateStringWithDefaultNotNullable = 'default';
    protected string $protectedStringWithDefaultNotNullable = 'default';
    public string $publicStringWithDefaultNotNullable = 'default';
    
    private ?string $privateStringWithoutDefaultNullable;
    protected ?string $protectedStringWithoutDefaultNullable;
    public ?string $publicStringWithoutDefaultNullable;

    private ?string $privateStringWithDefaultNullable = null;
    protected ?string $protectedStringWithDefaultNullable = null;
    public ?string $publicStringWithDefaultNullable = null;

    private $privateUntypedStringWithDefaultNotNullable = 'default';
    protected $protectedUntypedStringWithDefaultNotNullable = 'default';
    public $publicUntypedStringWithDefaultNotNullable = 'default';

    #[StringMatchesExpression('/foo/')]
    private string $stringWithAttrRegex;

    #[StringIsUuid()]
    private string $stringWithAttrUuid;

    #[StringIsUuid()]
    #[StringMatchesExpression('/f00/')]
    private string $stringWithAttrUuidAndRegex;

    #[StringIsEmail]
    private string $stringWithAttrEmail;

    #[StringIsEmail]
    #[StringMatchesExpression('/foo/')]
    private string $stringWithAttrEmailAndRegex;

    public function list(): array
    {
        return \get_object_vars($this);
    }
}
