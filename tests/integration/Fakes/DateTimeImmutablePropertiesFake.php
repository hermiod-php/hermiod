<?php

declare(strict_types=1);

namespace Hermiod\Tests\Integration\Fakes;

final class DateTimeImmutablePropertiesFake
{
    private \DateTimeImmutable $privateDateTimeImmutableWithoutDefaultNotNullable;
    protected \DateTimeImmutable $protectedDateTimeImmutableWithoutDefaultNotNullable;
    public \DateTimeImmutable $publicDateTimeImmutableWithoutDefaultNotNullable;
    
    private ?\DateTimeImmutable $privateDateTimeImmutableWithoutDefaultNullable;
    protected ?\DateTimeImmutable $protectedDateTimeImmutableWithoutDefaultNullable;
    public ?\DateTimeImmutable $publicDateTimeImmutableWithoutDefaultNullable;

    private ?\DateTimeImmutable $privateDateTimeImmutableWithDefaultNullable = null;
    protected ?\DateTimeImmutable $protectedDateTimeImmutableWithDefaultNullable = null;
    public ?\DateTimeImmutable $publicDateTimeImmutableWithDefaultNullable = null;

    public function list(): array
    {
        return \get_object_vars($this);
    }
}
