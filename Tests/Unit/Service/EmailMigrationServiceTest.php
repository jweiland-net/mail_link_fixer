<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/mail-link-fixer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\MailLinkFixer\Tests\Unit\Service;

use JWeiland\MailLinkFixer\Service\EmailMigrationService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for EmailMigrationService.
 *
 * Encoded strings were computed by applying the same Caesar cipher that
 * TYPO3's legacy linkTo_UnCryptMailto JavaScript function used.
 *
 * Range definitions (matching the decrypt logic):
 *   43–58  → +,-./0123456789:
 *   64–90  → @A-Z
 *   97–122 → a-z
 *
 * Encoding shifts each character forward by `offset` within its range (with wrap),
 * so decrypting shifts it back by the same offset.
 *
 * Pre-computed examples (offset=1):
 *   mailto:info@example.com  →  nbjmup+jogpAfybnqmf/dpn
 *
 * Pre-computed examples (offset=3):
 *   mailto:admin@test.org    →  pdorwr-dgplqCwhvw1ruj
 */
final class EmailMigrationServiceTest extends TestCase
{
    private EmailMigrationService $subject;

    protected function setUp(): void
    {
        $this->subject = new EmailMigrationService();
    }

    #[Test]
    public function fixBodyTextReturnsUnchangedStringWhenNoLegacyLinksPresent(): void
    {
        $input = '<p>Contact us at <a href="mailto:info@example.com">info@example.com</a></p>';
        $changes = [];

        $result = $this->subject->fixBodyText($input, $changes);

        self::assertSame($input, $result);
        self::assertSame([], $changes);
    }

    #[Test]
    public function fixBodyTextFixesSingleLegacyLinkWithOffset1(): void
    {
        // mailto:info@example.com encrypted with offset 1 = nbjmup+jogpAfybnqmf/dpn
        $input = '<p><a href="javascript:linkTo_UnCryptMailto(\'nbjmup+jogpAfybnqmf/dpn\')">info@example.com</a></p>';
        $changes = [];

        $result = $this->subject->fixBodyText($input, $changes);

        self::assertStringContainsString('<a href="mailto:info@example.com">info@example.com</a>', $result);
        self::assertStringNotContainsString('javascript:', $result);
        self::assertCount(1, $changes);
        self::assertSame('mailto:info@example.com', $changes[0]['decoded']);
    }

    #[Test]
    public function fixBodyTextFixesSingleLegacyLinkWithOffset3(): void
    {
        // mailto:admin@test.org encrypted with offset 3 = pdlowr-dgplqCwhvw1ruj
        $input = '<a href="javascript:linkTo_UnCryptMailto(\'pdlowr-dgplqCwhvw1ruj\')">admin@test.org</a>';
        $changes = [];

        $result = $this->subject->fixBodyText($input, $changes);

        self::assertStringContainsString('<a href="mailto:admin@test.org">admin@test.org</a>', $result);
        self::assertStringNotContainsString('javascript:', $result);
        self::assertCount(1, $changes);
    }

    #[Test]
    public function fixBodyTextFixesMultipleLegacyLinksInOneString(): void
    {
        // Two legacy links in the same bodytext
        $input = implode(' ', [
            '<a href="javascript:linkTo_UnCryptMailto(\'nbjmup+jogpAfybnqmf/dpn\')">info@example.com</a>',
            'and',
            '<a href="javascript:linkTo_UnCryptMailto(\'pdlowr-dgplqCwhvw1ruj\')">admin@test.org</a>',
        ]);
        $changes = [];

        $result = $this->subject->fixBodyText($input, $changes);

        self::assertStringContainsString('href="mailto:info@example.com"', $result);
        self::assertStringContainsString('href="mailto:admin@test.org"', $result);
        self::assertStringNotContainsString('javascript:', $result);
        self::assertCount(2, $changes);
    }

    #[Test]
    public function fixBodyTextPopulatesChangesArrayWithEncodedAndDecodedValues(): void
    {
        $input = '<a href="javascript:linkTo_UnCryptMailto(\'nbjmup+jogpAfybnqmf/dpn\')">info@example.com</a>';
        $changes = [];

        $this->subject->fixBodyText($input, $changes);

        self::assertArrayHasKey('encoded', $changes[0]);
        self::assertArrayHasKey('decoded', $changes[0]);
        self::assertArrayHasKey('result', $changes[0]);
        self::assertSame('nbjmup+jogpAfybnqmf/dpn', $changes[0]['encoded']);
        self::assertSame('mailto:info@example.com', $changes[0]['decoded']);
    }

    #[Test]
    public function fixBodyTextStripsAnchorTagAndKeepsLinkTextWhenDecodingFails(): void
    {
        // Completely random gibberish that cannot be decoded to a mailto: prefix
        $input = '<a href="javascript:linkTo_UnCryptMailto(\'zzzzzzzzzzzzzzzzzzzzzzz\')">contact us</a>';
        $changes = [];

        $result = $this->subject->fixBodyText($input, $changes);

        // Link text is preserved; the broken <a> wrapper is removed
        self::assertStringContainsString('contact us', $result);
        self::assertStringNotContainsString('javascript:', $result);
        self::assertStringNotContainsString('<a ', $result);
    }

    #[Test]
    public function fixBodyTextHandlesUnicodeEscapedAtSign(): void
    {
        // '@' is ASCII 64, its Unicode escape is @.
        // Encoding mailto:a@b.com with offset 1 would produce: nbjmup+bAc/dpn
        // But if the JS had @ for @ then the encoded param contains @ for the
        // encrypted '@' equivalent. We test the unescaping step independently by using
        // a param that has A (which is 'A') — encrypted '@' with offset 1.
        // So the full encoded param for mailto:info@example.com with @ escaped as A
        // in place of 'A' is: nbjmup+jogpAfybnqmf/dpn
        $input = '<a href="javascript:linkTo_UnCryptMailto(\'nbjmup+jogp\\u0041fybnqmf/dpn\')">info@example.com</a>';
        $changes = [];

        $result = $this->subject->fixBodyText($input, $changes);

        self::assertStringContainsString('href="mailto:info@example.com"', $result);
        self::assertCount(1, $changes);
    }

    #[Test]
    public function fixBodyTextPreservesContentOutsideLegacyLinks(): void
    {
        $input = '<p>Hello world</p><a href="javascript:linkTo_UnCryptMailto(\'nbjmup+jogpAfybnqmf/dpn\')">info@example.com</a><p>Footer</p>';
        $changes = [];

        $result = $this->subject->fixBodyText($input, $changes);

        self::assertStringContainsString('<p>Hello world</p>', $result);
        self::assertStringContainsString('<p>Footer</p>', $result);
        self::assertStringContainsString('href="mailto:info@example.com"', $result);
    }

    public static function legacyLinkWithAttributesDataProvider(): array
    {
        return [
            'link with class attribute' => [
                '<a class="email-link" href="javascript:linkTo_UnCryptMailto(\'nbjmup+jogpAfybnqmf/dpn\')">info@example.com</a>',
                'mailto:info@example.com',
            ],
            'link with title attribute' => [
                '<a href="javascript:linkTo_UnCryptMailto(\'nbjmup+jogpAfybnqmf/dpn\')" title="Send email">info@example.com</a>',
                'mailto:info@example.com',
            ],
        ];
    }

    #[Test]
    #[DataProvider('legacyLinkWithAttributesDataProvider')]
    public function fixBodyTextFixesLinksWithAdditionalHtmlAttributes(string $input, string $expectedDecoded): void
    {
        $changes = [];

        $result = $this->subject->fixBodyText($input, $changes);

        self::assertStringNotContainsString('javascript:', $result);
        self::assertCount(1, $changes);
        self::assertSame($expectedDecoded, $changes[0]['decoded']);
    }
}
