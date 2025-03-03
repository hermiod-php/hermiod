<?php

declare(strict_types=1);

namespace Hermiod\Tests\Integration\Fakes;

final class DateTimePropertiesFake
{
    private \DateTime $privateDateTimeWithoutDefaultNotNullable;
    protected \DateTime $protectedDateTimeWithoutDefaultNotNullable;
    public \DateTime $publicDateTimeWithoutDefaultNotNullable;
    
    private ?\DateTime $privateDateTimeWithoutDefaultNullable;
    protected ?\DateTime $protectedDateTimeWithoutDefaultNullable;
    public ?\DateTime $publicDateTimeWithoutDefaultNullable;

    private ?\DateTime $privateDateTimeWithDefaultNullable = null;
    protected ?\DateTime $protectedDateTimeWithDefaultNullable = null;
    public ?\DateTime $publicDateTimeWithDefaultNullable = null;

    public function list(): array
    {
        return \get_object_vars($this);
    }
}
