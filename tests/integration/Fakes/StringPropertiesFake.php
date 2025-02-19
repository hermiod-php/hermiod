<?php

declare(strict_types=1);

namespace Hermiod\Tests\Integration\Fakes;

use Hermiod\Attribute\Constraint\StringIsEmail;
use Hermiod\Attribute\Constraint\StringIsUuid;
use Hermiod\Attribute\Constraint\StringMatchesRegex;

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

    #[StringMatchesRegex('/foo/')]
    private string $stringWithAttrRegex;

    #[StringIsUuid()]
    private string $stringWithAttrUuid;

    #[StringIsUuid()]
    #[StringMatchesRegex('/f00/')]
    private string $stringWithAttrUuidAndRegex;

    #[StringIsEmail]
    private string $stringWithAttrEmail;

    #[StringIsEmail]
    #[StringMatchesRegex('/foo/')]
    private string $stringWithAttrEmailAndRegex;

    public function list(): array
    {
        return \get_object_vars($this);
    }
}
