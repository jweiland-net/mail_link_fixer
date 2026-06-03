..  include:: /Includes.rst.txt


..  _faq:

===
FAQ
===

Does this extension support TYPO3 12 or older?
===============================================

No. ``EXT:mail_link_fixer`` requires TYPO3 **13.4 LTS** or later and PHP **8.2+**.

If you need to run the migration on a TYPO3 12 site, consider upgrading TYPO3 first or
writing a custom migration script using the same decryption logic.


Will the command delete or damage any data?
===========================================

No. The command only modifies the specific HTML string inside the scanned field. It
replaces the ``<a href="javascript:linkTo_UnCryptMailto(...)">`` tag with
``<a href="mailto:...">`` and leaves all surrounding content untouched.

Always use ``--dry-run`` first (see :ref:`bestPractice`) and back up your database
before running the live migration.


What happens if an encoded link cannot be decrypted?
====================================================

The command tries all Caesar-cipher offsets from 1 to 10 in both directions. If none
produces a string starting with ``mailto:``, the link is considered undecodable.

In that case the ``<a>`` element is stripped and only its visible inner text is kept.
No broken HTML or JavaScript remains in the database. The original undecodable content is
preserved in a ``changes`` log entry that you can review with ``-v`` (verbose output).


Can I run the command more than once?
======================================

Yes. The command only matches the ``javascript:linkTo_UnCryptMailto`` pattern. Records
that have already been migrated no longer contain this pattern, so a second run will
report zero records found and make no changes.


My email contains a ``+`` character (e.g. ``user+tag@example.com``). Does it work?
===================================================================================

Yes. The ``+`` character (ASCII 43) falls within the encoded character range and is
handled by the decryption algorithm. As long as the result passes TYPO3's email
validator, the address is written correctly.


Why does the command not fix links in a custom table?
======================================================

By default the command processes ``tt_content.bodytext``. To target a custom table and
field, use ``--table`` and ``--field``:

..  code-block:: bash

    vendor/bin/typo3 mail_link_fixer:fix-spam-email --table=tx_news_domain_model_news --field=bodytext

To process **all** TCA-registered RTE and text fields at once, use ``--all-rte``.


Do I need to configure anything in extension settings?
======================================================

No. There are no extension settings, FlexForm options, TypoScript constants, or backend
modules. The extension is purely a CLI tool.


How do I know the migration was successful?
===========================================

1.  Run the command with ``--dry-run -v`` and note the record UIDs and replacement counts.
2.  After the live run, re-run with ``--dry-run``. The output should show
    ``No records found containing legacy links.`` for every table/field.
3.  Open affected pages in a browser and verify that email links open a mail client
    correctly.


The extension installed but the command is not listed. What should I do?
=========================================================================

Run ``vendor/bin/typo3 cache:flush`` to clear all caches, then run
``vendor/bin/typo3 list`` again. If the command is still missing, verify that
``Configuration/Services.yaml`` is present in the extension directory and that the
extension is active in the ExtensionManager.