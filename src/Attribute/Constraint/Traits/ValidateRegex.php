<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint\Traits;

use Hermiod\Attribute\Constraint\Exception\InvalidRegexException;

trait ValidateRegex
{
    private function validateRegex(string $regex): void
    {
        if (@\preg_match($regex, '') === false) {
            $error = \error_get_last() ?? ['message' => ''];

            throw InvalidRegexException::invalidRegex(
                $regex,
                (string)\preg_replace('/^preg_match\(\): /', '', $error['message']),
            );
        }
    }
}
