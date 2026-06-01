<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/resolve-unsecure-mail.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\ResolveUnsecureMail\Command;

use Doctrine\DBAL\Exception;
use JWeiland\ResolveUnsecureMail\Domain\Repository\LegacyLinkRepository;
use JWeiland\ResolveUnsecureMail\Service\EmailMigrationService;
use JWeiland\ResolveUnsecureMail\Service\TcaScannerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Fixes <a href="javascript:linkTo_UnCryptMailto('...')"> links stored in RTE fields.
 */
#[AsCommand(
    name: 'resolve_unsecure_mail:fix-spam-email',
    description: 'A command that fixes spam protected email links in RTE fields.',
)]
final class FixSpamProtectedEmailCommand extends Command
{
    public function __construct(
        private readonly LegacyLinkRepository $linkRepository,
        private readonly TcaScannerService $tcaScanner,
        private readonly EmailMigrationService $migrationService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Preview changes without writing to the database.')
            ->addOption('uid', null, InputOption::VALUE_REQUIRED, 'Process only the tt_content record with this UID.')
            ->addOption(
                'table',
                't',
                InputOption::VALUE_OPTIONAL,
                'Specific table to process (defaults to tt_content).',
                'tt_content',
            )
            ->addOption(
                'field',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Specific field to process (defaults to bodytext).',
                'bodytext',
            )
            ->addOption(
                'all-rte',
                null,
                InputOption::VALUE_NONE,
                'Scan ALL tables and fields in TCA configured with enableRichtext=true.',
            );
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool)$input->getOption('dry-run');
        $limitUid = $input->getOption('uid') !== null ? (int)$input->getOption('uid') : null;

        if ($dryRun) {
            $io->writeln('<comment>[DRY RUN] No changes will be written to the database.</comment>');
        }

        $targets = $this->getTargetFields($input);
        if ($targets === []) {
            $io->error('No valid tables/fields found to process.');
            return Command::FAILURE;
        }

        $globalCountFixed = 0;
        $globalCountUnchanged = 0;

        foreach ($targets as $targetTable => $targetFields) {
            foreach ($targetFields as $targetField) {
                $io->section(sprintf('Processing %s.%s', $targetTable, $targetField));

                // USE THE REPOSITORY
                $records = $this->linkRepository->findRecordsWithObsoleteLinks($targetTable, $targetField, $limitUid);

                if ($records === []) {
                    $io->writeln('  No records found containing legacy links.');
                    continue;
                }

                $tableCountFixed = 0;
                foreach ($records as $record) {
                    $changes = [];
                    $updated = $this->migrationService->fixBodyText((string)$record[$targetField], $changes);

                    if ($changes === []) {
                        $globalCountUnchanged++;
                        continue;
                    }

                    if ($io->isVerbose()) {
                        $io->writeln(sprintf('  UID %d: %d replacement(s)', $record['uid'], count($changes)));
                    }

                    // USE THE REPOSITORY
                    if (!$dryRun) {
                        $this->linkRepository->updateRecordField($targetTable, $targetField, (int)$record['uid'], $updated);
                    }

                    $tableCountFixed++;
                    $globalCountFixed++;
                }

                $io->success(sprintf('Fixed %d records in %s.%s', $tableCountFixed, $targetTable, $targetField));
            }
        }

        $io->info(sprintf('%sMigration complete. Fixed: %d | Clean: %d', $dryRun ? '[DRY RUN] ' : '', $globalCountFixed, $globalCountUnchanged));

        return Command::SUCCESS;
    }

    private function getTargetFields(InputInterface $input): array
    {
        if ($input->getOption('all-rte')) {
            return $this->tcaScanner->findAllRteAndTextFields();
        }

        return [
            (string)$input->getOption('table') => [(string)$input->getOption('field')],
        ];
    }
}
