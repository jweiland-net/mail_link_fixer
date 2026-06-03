..  include:: /Includes.rst.txt


..  _bestPractice:

=============
Best Practice
=============

This chapter contains recommended procedures for running the mail link migration safely.

Always use ``--dry-run`` first
==============================

Before making any changes to the live database, run the command with ``--dry-run`` to
preview all replacements without writing anything:

..  code-block:: bash

    vendor/bin/typo3 mail_link_fixer:fix-spam-email --all-rte --dry-run -v

Review the output carefully. Each replaced link will show the record UID and the number
of substitutions. Only proceed to a real run when the preview output looks correct.

Back up the database before the migration run
=============================================

Although the command only modifies the specific fields it scans, a full database backup
before running any migration is strongly recommended:

..  code-block:: bash

    mysqldump -u root -p my_database > backup_before_mail_link_fixer.sql

Test on a staging environment first
====================================

Run the migration on a staging copy of the database before applying it to production.
This lets you verify the results in a real TYPO3 frontend without risk.

Use ``--uid`` for spot-checking
================================

To verify a specific record looks correct after migration, use ``--uid``:

..  code-block:: bash

    vendor/bin/typo3 mail_link_fixer:fix-spam-email --uid=123 --dry-run -v

Then remove ``--dry-run`` to apply the fix to that single record before running a
full migration.

Use ``--all-rte`` carefully
============================

The ``--all-rte`` flag scans every TCA-registered table that has RTE or text fields.
On large sites with many extensions, this can touch dozens of tables. Consider running
table-by-table (using ``--table`` and ``--field``) if you want finer control over which
data is processed.

Verify results in the frontend
================================

After the migration, open pages that contained the legacy links in a browser and verify
that email addresses render as clickable ``mailto:`` links and that no broken JavaScript
anchors remain.

The command is safe to re-run
==============================

Because the command searches specifically for the ``javascript:linkTo_UnCryptMailto``
pattern, re-running it on already-migrated content is harmless. Records without the
pattern are skipped without modification.