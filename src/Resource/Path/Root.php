<?php

declare(strict_types=1);

namespace Hermiod\Resource\Path;

use Hermiod\Resource\Path\Exception\EmptyJsonPathObjectKeyException;

/**
 * @no-named-arguments No backwards compatibility guaranteed
 * @internal No backwards compatibility guaranteed
 */
final class Root implements PathInterface
{
    private const ESCAPE_CHARACTERS = '/^[\d\W]|[\s\[\]\{\}\(\),;\'"``~\-!@#$%^&*+=<>?\/\\\\]|\./';

    /**
     * @var string[]
     */
    private array $path = ['$'];

    public function __toString(): string
    {
        return \implode('', $this->path);
    }

    public function withObjectKey(string $key): Root
    {
        $key = \trim($key);

        if (\strlen($key) === 0) {
            throw EmptyJsonPathObjectKeyException::new($this, $key);
        }

        $copy = clone $this;

        $copy->path[] = \preg_match(self::ESCAPE_CHARACTERS, $key)
            ? \sprintf('["%s"]', $key)
            : \sprintf('.%s', $key);

        return $copy;
    }

    public function withArrayKey(int $key): Root
    {
        $copy = clone $this;

        $copy->path[] = \sprintf('[%s]', $key);

        return $copy;
    }
}