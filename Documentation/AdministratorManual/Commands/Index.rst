..  include:: /Includes.rst.txt


..  _commands:

========
Commands
========

mail_link_fixer:fix-spam-email
==============================

This is the only CLI command provided by the extension. It scans one or more database
fields for ``javascript:linkTo_UnCryptMailto(...)`` hrefs, decrypts the encoded email
addresses, and replaces them with clean ``mailto:`` links.

..  code-block:: bash

    vendor/bin/typo3 mail_link_fixer:fix-spam-email [options]

Options
-------

.. list-table::
   :header-rows: 1
   :widths: 25 10 65

   * - Option
     - Short
     - Description
   * - ``--dry-run``
     -
     - Preview all changes without writing anything to the database. Always run this
       first.
   * - ``--uid=<UID>``
     -
     - Process only the record with this UID in the target table. Useful for
       spot-checking a single record before a full run.
   * - ``--table=<table>``
     - ``-t``
     - The database table to process. Defaults to ``tt_content``.
   * - ``--field=<field>``
     - ``-f``
     - The field within the table to process. Defaults to ``bodytext``.
   * - ``--all-rte``
     -
     - Scan **all** tables and fields registered in TCA with ``enableRichtext=true``
       or ``type=text``. Overrides ``--table`` and ``--field``.

Exit codes
----------

*   ``0`` — success, migration completed (or dry-run preview completed)
*   ``1`` — failure, no valid tables/fields found to process

Output verbosity
----------------

By default the command prints a section header and a success line per
table/field combination. Add ``-v`` (verbose) to see the number of replacements
per record UID.

Examples
--------

Preview all changes without touching the database
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Always run a dry-run first to understand what will change:

..  code-block:: bash

    vendor/bin/typo3 mail_link_fixer:fix-spam-email --dry-run

Verbose dry-run to see individual record replacements
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

..  code-block:: bash

    vendor/bin/typo3 mail_link_fixer:fix-spam-email --dry-run -v

Fix all records in the default table and field (tt_content.bodytext)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

..  code-block:: bash

    vendor/bin/typo3 mail_link_fixer:fix-spam-email

Fix a single record by UID
^^^^^^^^^^^^^^^^^^^^^^^^^^

..  code-block:: bash

    vendor/bin/typo3 mail_link_fixer:fix-spam-email --uid=42

Fix a specific table and field
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

..  code-block:: bash

    vendor/bin/typo3 mail_link_fixer:fix-spam-email --table=tx_myext_domain_model_article --field=bodytext

Scan and fix all RTE fields across the entire TCA
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

..  code-block:: bash

    vendor/bin/typo3 mail_link_fixer:fix-spam-email --all-rte

Combine ``--all-rte`` with ``--dry-run`` for a safe full-site preview
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

..  code-block:: bash

    vendor/bin/typo3 mail_link_fixer:fix-spam-email --all-rte --dry-run

..  tip::

    The command is idempotent. Running it a second time on already-migrated records is
    safe: no ``javascript:linkTo_UnCryptMailto`` patterns will be found and nothing will
    be changed.

How the decryption works
------------------------

TYPO3's legacy frontend spam protection applied a Caesar cipher over three character
ranges (``+,-./0-9:``, ``@A-Z``, ``a-z``) with a random offset between 1 and 10.

The command tries all offsets from 1 to 10 (both positive and negative) until a decoded
string that starts with ``mailto:`` is found. The decoded email address is then validated
using TYPO3's built-in email validator before being written to the database.

If a link cannot be decrypted (no valid ``mailto:`` prefix can be recovered), the
``<a>`` element is stripped and only its visible link text is preserved, so no broken
HTML remains in the database.

DDEV usage
----------

If you are using DDEV, prefix the command with ``ddev exec``:

..  code-block:: bash

    ddev exec vendor/bin/typo3 mail_link_fixer:fix-spam-email --dry-run