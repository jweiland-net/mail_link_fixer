<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/mail-link-fixer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\MailLinkFixer\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service responsible for parsing RTE text and decrypting legacy TYPO3 mailto links.
 */
final readonly class EmailMigrationService
{
    private const string PATTERN_A_TAG = '/<a\s[^>]*href\s*=\s*["\']javascript:linkTo_UnCryptMailto\(\'([^\']+)\'\)[^>]*>(.*?)<\/a>/is';

    public function fixBodyText(string $bodytext, array &$changes): string
    {
        return preg_replace_callback(
            self::PATTERN_A_TAG,
            function (array $matches) use (&$changes): string {
                $encodedParam = $this->unEscapeUnicode($matches[1]);
                $linkText = $matches[2];

                $decoded = $this->tryDecode($encodedParam);
                $result = $this->buildReplacement($decoded, $linkText);

                $changes[] = [
                    'encoded' => $encodedParam,
                    'decoded' => $decoded,
                    'result' => $result,
                ];

                return $result;
            },
            $bodytext,
        ) ?? $bodytext;
    }

    private function buildReplacement(?string $decoded, string $linkText): string
    {
        if ($decoded === null) {
            return $linkText;
        }

        $emailPart = substr($decoded, strlen('mailto:'));
        $emails = GeneralUtility::trimExplode(',', $emailPart, true);
        $validEmails = [];

        foreach ($emails as $email) {
            if (GeneralUtility::validEmail($email)) {
                $validEmails[] = $email;
            }
        }

        if ($validEmails !== []) {
            $safeMailto = htmlspecialchars(implode(',', $validEmails), ENT_QUOTES, 'UTF-8');
            return sprintf('<a href="mailto:%s">%s</a>', $safeMailto, $linkText);
        }

        return $linkText;
    }

    private function tryDecode(string $encoded): ?string
    {
        for ($offset = 1; $offset <= 10; $offset++) {
            $decoded = $this->decryptEmail($encoded, $offset);
            if (str_starts_with($decoded, 'mailto:')) {
                return $decoded;
            }
            $decoded = $this->decryptEmail($encoded, -$offset);
            if (str_starts_with($decoded, 'mailto:')) {
                return $decoded;
            }
        }

        return null;
    }

    private function decryptEmail(string $encoded, int $offset): string
    {
        $decrypted = '';
        $encodedStringLength = strlen($encoded);

        for ($i = 0; $i < $encodedStringLength; $i++) {
            $asciiCode = ord($encoded[$i]);
            if ($asciiCode >= 43 && $asciiCode <= 58) {
                $decrypted .= $this->decryptCharCode($asciiCode, 43, 58, $offset);
            } elseif ($asciiCode >= 64 && $asciiCode <= 90) {
                $decrypted .= $this->decryptCharCode($asciiCode, 64, 90, $offset);
            } elseif ($asciiCode >= 97 && $asciiCode <= 122) {
                $decrypted .= $this->decryptCharCode($asciiCode, 97, 122, $offset);
            } else {
                $decrypted .= $encoded[$i];
            }
        }

        return $decrypted;
    }

    private function decryptCharCode(int $asciiCode, int $rangeStart, int $rangeEnd, int $offset): string
    {
        $shiftedCode = $asciiCode - $offset;

        if ($offset > 0 && $shiftedCode < $rangeStart) {
            $underflowAmount = $rangeStart - $shiftedCode;
            $shiftedCode = $rangeEnd - $underflowAmount + 1;
        } elseif ($offset < 0 && $shiftedCode > $rangeEnd) {
            $overflowAmount = $shiftedCode - $rangeEnd;
            $shiftedCode = $rangeStart + $overflowAmount - 1;
        }

        return chr($shiftedCode);
    }

    private function unEscapeUnicode(string $encodedString): string
    {
        $unicodePattern = '/\\\\u([0-9a-fA-F]{4})/';

        return (string)preg_replace_callback(
            $unicodePattern,
            static function (array $matches): string {
                [$fullMatch, $hexValue] = $matches;

                $decimalValue = (int)hexdec($hexValue);
                $utf8Char = mb_chr($decimalValue, 'UTF-8');

                return $utf8Char !== false ? $utf8Char : $fullMatch;
            },
            $encodedString,
        );
    }
}
