<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/mail-link-fixer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\MailLinkFixer\Tests\Unit\Service;

use JWeiland\MailLinkFixer\Service\TcaScannerService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Schema\Field\FieldCollection;
use TYPO3\CMS\Core\Schema\Field\FieldTypeInterface;
use TYPO3\CMS\Core\Schema\SchemaCollection;
use TYPO3\CMS\Core\Schema\TcaSchema;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;

final class TcaScannerServiceTest extends TestCase
{
    private TcaSchemaFactory&MockObject $tcaSchemaFactory;
    private TcaScannerService $subject;

    protected function setUp(): void
    {
        $this->tcaSchemaFactory = $this->createMock(TcaSchemaFactory::class);
        $this->subject = new TcaScannerService($this->tcaSchemaFactory);
    }

    #[Test]
    public function findAllRteAndTextFieldsReturnsEmptyArrayWhenNoTablesRegistered(): void
    {
        $this->tcaSchemaFactory
            ->expects(self::atLeastOnce())->method('all')
            ->willReturn(new SchemaCollection([]));

        $result = $this->subject->findAllRteAndTextFields();

        self::assertSame([], $result);
    }

    #[Test]
    public function findAllRteAndTextFieldsReturnsRteEnabledFields(): void
    {
        $rteField = $this->createFieldMock('bodytext', ['enableRichtext' => true, 'type' => 'text']);
        $schema = $this->createSchemaMock('tt_content', [$rteField]);

        $this->tcaSchemaFactory->expects(self::atLeastOnce())->method('all')->willReturn(
            new SchemaCollection(['tt_content' => $schema])
        );

        $result = $this->subject->findAllRteAndTextFields();

        self::assertArrayHasKey('tt_content', $result);
        self::assertContains('bodytext', $result['tt_content']);
    }

    #[Test]
    public function findAllRteAndTextFieldsReturnsPlainTextFields(): void
    {
        $textField = $this->createFieldMock('description', ['type' => 'text']);
        $schema = $this->createSchemaMock('tx_myext_domain_model_article', [$textField]);

        $this->tcaSchemaFactory->expects(self::atLeastOnce())->method('all')->willReturn(
            new SchemaCollection(['tx_myext_domain_model_article' => $schema])
        );

        $result = $this->subject->findAllRteAndTextFields();

        self::assertArrayHasKey('tx_myext_domain_model_article', $result);
        self::assertContains('description', $result['tx_myext_domain_model_article']);
    }

    #[Test]
    public function findAllRteAndTextFieldsIgnoresNonTextFields(): void
    {
        $inputField = $this->createFieldMock('title', ['type' => 'input']);
        $schema = $this->createSchemaMock('tt_content', [$inputField]);

        $this->tcaSchemaFactory->expects(self::atLeastOnce())->method('all')->willReturn(
            new SchemaCollection(['tt_content' => $schema])
        );

        $result = $this->subject->findAllRteAndTextFields();

        self::assertSame([], $result);
    }

    #[Test]
    public function findAllRteAndTextFieldsCollectsFieldsFromMultipleTables(): void
    {
        $rteField = $this->createFieldMock('bodytext', ['enableRichtext' => true, 'type' => 'text']);
        $textField = $this->createFieldMock('description', ['type' => 'text']);

        $schema1 = $this->createSchemaMock('tt_content', [$rteField]);
        $schema2 = $this->createSchemaMock('tx_myext_domain_model_news', [$textField]);

        $this->tcaSchemaFactory->expects(self::atLeastOnce())->method('all')->willReturn(new SchemaCollection([
            'tt_content' => $schema1,
            'tx_myext_domain_model_news' => $schema2,
        ]));

        $result = $this->subject->findAllRteAndTextFields();

        self::assertArrayHasKey('tt_content', $result);
        self::assertArrayHasKey('tx_myext_domain_model_news', $result);
        self::assertContains('bodytext', $result['tt_content']);
        self::assertContains('description', $result['tx_myext_domain_model_news']);
    }

    #[Test]
    public function findAllRteAndTextFieldsDoesNotIncludeFieldWithEnableRichtextFalse(): void
    {
        $nonRteField = $this->createFieldMock('bodytext', ['enableRichtext' => false, 'type' => 'input']);
        $schema = $this->createSchemaMock('tt_content', [$nonRteField]);

        $this->tcaSchemaFactory->expects(self::atLeastOnce())->method('all')->willReturn(
            new SchemaCollection(['tt_content' => $schema])
        );

        $result = $this->subject->findAllRteAndTextFields();

        self::assertSame([], $result);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function createFieldMock(string $fieldName, array $config): FieldTypeInterface&MockObject
    {
        $field = $this->createMock(FieldTypeInterface::class);
        $field->expects(self::atLeastOnce())->method('getName')->willReturn($fieldName);
        $field->expects(self::atLeastOnce())->method('getConfiguration')->willReturn($config);
        return $field;
    }

    private function createSchemaMock(string $tableName, array $fields): TcaSchema&MockObject
    {
        $schema = $this->createMock(TcaSchema::class);
        $schema->expects(self::atLeastOnce())->method('getName')->willReturn($tableName);
        $fieldsByName = [];
        foreach ($fields as $field) {
            $fieldsByName[$field->getName()] = $field;
        }
        $schema->expects(self::atLeastOnce())->method('getFields')->willReturn(new FieldCollection($fieldsByName));

        return $schema;
    }
}
