..  include:: /Includes.rst.txt


..  _changelog:

=========
ChangeLog
=========

Version 1.0.0
=============

Initial release.

*   [FEATURE] Add ``mail_link_fixer:fix-spam-email`` CLI command to migrate legacy
    ``javascript:linkTo_UnCryptMailto()`` href attributes to standard ``mailto:`` links
*   [FEATURE] Add ``--dry-run`` option to preview changes without writing to the database
*   [FEATURE] Add ``--uid`` option to limit processing to a single record
*   [FEATURE] Add ``--table`` / ``-t`` option to target a specific database table
*   [FEATURE] Add ``--field`` / ``-f`` option to target a specific field within a table
*   [FEATURE] Add ``--all-rte`` option to scan all TCA-registered RTE and text fields
*   [FEATURE] Add ``TcaScannerService`` to dynamically discover all RTE/text fields from
    the TYPO3 TCA schema
*   [FEATURE] Add ``EmailMigrationService`` with Caesar-cipher decryption supporting
    offsets 1–10 in both directions and Unicode-escape preprocessing
*   [FEATURE] Add ``LegacyLinkRepository`` for efficient database reads and updates using
    TYPO3 ``ConnectionPool``
*   [TASK] Require TYPO3 13.4 LTS or 14.3 LTS, PHP 8.2+