<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/mail-link-fixer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\MailLinkFixer\Tests\Functional\Domain\Repository;

use JWeiland\MailLinkFixer\Domain\Repository\LegacyLinkRepository;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class LegacyLinkRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['mail_link_fixer'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/Database/tt_content.csv');
    }

    private function getSubject(): LegacyLinkRepository
    {
        return GeneralUtility::makeInstance(
            LegacyLinkRepository::class,
            GeneralUtility::makeInstance(ConnectionPool::class),
        );
    }

    #[Test]
    public function findRecordsWithObsoleteLinksReturnsRecordsContainingLegacyLinks(): void
    {
        $subject = $this->getSubject();

        $records = $subject->findRecordsWithObsoleteLinks('tt_content', 'bodytext', null);

        // uid 1 and 3 contain the pattern; uid 4 is deleted (excluded by DeletedRestriction)
        self::assertCount(2, $records);
        $uids = array_column($records, 'uid');
        self::assertContains(1, array_map('intval', $uids));
        self::assertContains(3, array_map('intval', $uids));
    }

    #[Test]
    public function findRecordsWithObsoleteLinksDoesNotReturnDeletedRecords(): void
    {
        $subject = $this->getSubject();

        $records = $subject->findRecordsWithObsoleteLinks('tt_content', 'bodytext', null);

        $uids = array_map('intval', array_column($records, 'uid'));
        self::assertNotContains(4, $uids, 'Deleted record (uid=4) must not be returned');
    }

    #[Test]
    public function findRecordsWithObsoleteLinksReturnsEmptyArrayWhenNoPatternFound(): void
    {
        $subject = $this->getSubject();

        // uid=2 has no legacy link pattern, so if we restrict to uid=2 we get nothing
        $records = $subject->findRecordsWithObsoleteLinks('tt_content', 'bodytext', 2);

        self::assertSame([], $records);
    }

    #[Test]
    public function findRecordsWithObsoleteLinksByUidReturnsSingleRecord(): void
    {
        $subject = $this->getSubject();

        $records = $subject->findRecordsWithObsoleteLinks('tt_content', 'bodytext', 1);

        self::assertCount(1, $records);
        self::assertSame(1, (int)$records[0]['uid']);
    }

    #[Test]
    public function findRecordsWithObsoleteLinksReturnsFieldValueInResult(): void
    {
        $subject = $this->getSubject();

        $records = $subject->findRecordsWithObsoleteLinks('tt_content', 'bodytext', 1);

        self::assertArrayHasKey('bodytext', $records[0]);
        self::assertStringContainsString('javascript:linkTo_UnCryptMailto', (string)$records[0]['bodytext']);
    }

    #[Test]
    public function updateRecordFieldWritesNewValueToDatabase(): void
    {
        $subject = $this->getSubject();
        $newValue = '<a href="mailto:info@example.com">info@example.com</a>';

        $subject->updateRecordField('tt_content', 'bodytext', 1, $newValue);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');
        $row = $connection->select(['bodytext'], 'tt_content', ['uid' => 1])->fetchAssociative();

        self::assertSame($newValue, $row['bodytext']);
    }

    #[Test]
    public function updateRecordFieldDoesNotAffectOtherRecords(): void
    {
        $subject = $this->getSubject();
        $newValue = '<a href="mailto:info@example.com">info@example.com</a>';

        $subject->updateRecordField('tt_content', 'bodytext', 1, $newValue);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tt_content');

        // uid=3 should still have the original legacy link
        $row = $connection->select(['bodytext'], 'tt_content', ['uid' => 3])->fetchAssociative();
        self::assertStringContainsString('javascript:linkTo_UnCryptMailto', (string)$row['bodytext']);
    }
}