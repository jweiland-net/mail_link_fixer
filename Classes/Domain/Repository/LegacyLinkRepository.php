<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/mail-link-fixer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\MailLinkFixer\Domain\Repository;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Repository responsible for fetching and updating records containing legacy spam-protected links.
 */
final readonly class LegacyLinkRepository
{
    public function __construct(private ConnectionPool $connectionPool) {}

    /**
     * @throws Exception
     */
    public function findRecordsWithObsoleteLinks(string $tableName, string $fieldName, ?int $limitUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($tableName);

        // Safely apply soft-delete restrictions based on TCA schema
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $queryBuilder
            ->select('uid', 'pid', $fieldName)
            ->from($tableName)
            ->where(
                $queryBuilder->expr()->like(
                    $fieldName,
                    $queryBuilder->createNamedParameter('%javascript:linkTo_UnCryptMailto%'),
                ),
            );

        if ($limitUid !== null) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($limitUid, Connection::PARAM_INT)),
            );
        }

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    /**
     * @throws Exception
     */
    public function updateRecordField(string $tableName, string $fieldName, int $uid, string $newValue): void
    {
        $this->connectionPool
            ->getConnectionForTable($tableName)
            ->update(
                $tableName,
                [$fieldName => $newValue],
                ['uid' => $uid],
            );
    }
}
