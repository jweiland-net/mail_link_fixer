<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/mail-link-fixer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\MailLinkFixer\Service;

use TYPO3\CMS\Core\Schema\TcaSchemaFactory;

/**
 * Service responsible for analyzing the TYPO3 TCA schema.
 */
final readonly class TcaScannerService
{
    public function __construct(private TcaSchemaFactory $tcaSchemaFactory) {}

    /**
     * @return array<string, string[]>
     */
    public function findAllRteAndTextFields(): array
    {
        $targets = [];

        foreach ($this->tcaSchemaFactory->all() as $tcaSchema) {
            $tableName = $tcaSchema->getName();
            foreach ($tcaSchema->getFields() as $field) {
                $fieldName = $field->getName();
                $config = $field->getConfiguration();
                $isRte = isset($config['enableRichtext']) && $config['enableRichtext'] === true;
                $isTextArea = isset($config['type']) && $config['type'] === 'text';
                if ($isRte || $isTextArea) {
                    $targets[$tableName][] = $fieldName;
                }
            }
        }

        return $targets;
    }
}
