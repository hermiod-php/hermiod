<?php

declare(strict_types=1);

namespace Hermiod\Attribute\Constraint;

use Hermiod\Resource\Path\PathInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class StringIsEmail implements StringConstraintInterface
{
    public function valueMatchesConstraint(string $value): bool
    {
        // First try PHP's built-in filter for standard emails
        if (false !== \filter_var($value, \FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        // Additional validation for modern email formats
        return $this->validateExtendedEmailFormats($value);
    }

    public function getMismatchExplanation(PathInterface $path, string $value): string
    {
        return \sprintf(
            "%s must be an email address but '%s' given",
            $path->__toString(),
            $value,
        );
    }

    private function validateExtendedEmailFormats(string $email): bool
    {
        // Basic structure check
        if (!\str_contains($email, '@') || \substr_count($email, '@') !== 1) {
            return false;
        }

        // Check for control characters and security vulnerabilities
        if ($this->containsSecurityVulnerabilities($email)) {
            return false;
        }

        [$localPart, $domain] = \explode('@', $email, 2);

        // Validate local part
        if (!$this->isValidLocalPart($localPart)) {
            return false;
        }

        // Validate domain part
        return $this->isValidDomain($domain);
    }

    private function containsSecurityVulnerabilities(string $email): bool
    {
        // Check for control characters (newlines, tabs, null bytes, etc.)
        if (\preg_match('/[\x00-\x1F\x7F]/', $email)) {
            return true;
        }

        // Check for homograph attacks - mixed scripts that could be confusing
        if ($this->containsHomographAttack($email)) {
            return true;
        }

        // Check for suspicious Unicode characters (zero-width, directional override, etc.)
        if (\preg_match('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{2064}]/u', $email)) {
            return true;
        }

        return false;
    }

    private function containsHomographAttack(string $email): bool
    {
        // Simple heuristic: if email contains both Latin and Cyrillic characters
        // this could be a homograph attack
        $hasLatin = \preg_match('/[a-zA-Z]/', $email);
        $hasCyrillic = \preg_match('/[\p{Cyrillic}]/u', $email);

        // Only flag as suspicious if BOTH scripts are present (mixed scripts)
        return $hasLatin && $hasCyrillic;
    }

    private function isValidLocalPart(string $localPart): bool
    {
        if ($localPart === '' || \strlen($localPart) > 64) {
            return false;
        }

        // Handle quoted local parts
        if (\str_starts_with($localPart, '"') && \str_ends_with($localPart, '"')) {
            return $this->isValidQuotedLocalPart($localPart);
        }

        // Unquoted local part validation
        if (\str_starts_with($localPart, '.') || \str_ends_with($localPart, '.')) {
            return false;
        }

        if (\str_contains($localPart, '..')) {
            return false;
        }

        // Allow Unicode characters including emojis and standard email characters
        return \preg_match('/^[\p{L}\p{N}\p{So}._+-]+$/u', $localPart) === 1;
    }

    private function isValidQuotedLocalPart(string $localPart): bool
    {
        $content = \substr($localPart, 1, -1);

        // Check for unescaped quotes inside
        return !\preg_match('/(?<!\\\\)"/', $content);
    }

    private function isValidDomain(string $domain): bool
    {
        if ($domain === '' || \strlen($domain) > 253) {
            return false;
        }

        // Handle IPv6 literal addresses
        if (\str_starts_with($domain, '[') && \str_ends_with($domain, ']')) {
            return $this->isValidIpV6Literal($domain);
        }

        // Regular domain validation
        if (\str_starts_with($domain, '.') || \str_ends_with($domain, '.')) {
            return false;
        }

        if (\str_contains($domain, '..')) {
            return false;
        }

        // Split into labels and validate each
        $labels = \explode('.', $domain);

        if (\count($labels) < 2) {
            return false;
        }

        foreach ($labels as $label) {
            if (!$this->isValidDomainLabel($label)) {
                return false;
            }
        }

        return true;
    }

    private function isValidIpV6Literal(string $domain): bool
    {
        $ip = \substr($domain, 1, -1);

        // IPv6 validation
        if (\filter_var($ip, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6) !== false) {
            return true;
        }

        return false;
    }

    private function isValidDomainLabel(string $label): bool
    {
        if ($label === '' || \strlen($label) > 63) {
            return false;
        }

        if (\str_starts_with($label, '-') || \str_ends_with($label, '-')) {
            return false;
        }

        // Allow Unicode characters in domain labels (IDN)
        return \preg_match('/^[\p{L}\p{N}-]+$/u', $label) === 1;
    }
}
