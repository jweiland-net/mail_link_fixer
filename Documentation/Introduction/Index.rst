..  include:: /Includes.rst.txt


..  _introduction:

============
Introduction
============

What does it do?
================

**Mail Link Fixer** (``EXT:mail_link_fixer``) is a TYPO3 backend CLI extension that migrates
legacy spam-protected email links stored in Rich Text (RTE) database fields to standard,
browser-native ``mailto:`` links.

Background
----------

In older TYPO3 installations (version 6–10), the frontend spam protection feature
automatically obfuscated email addresses in RTE content using a JavaScript Caesar cipher.
These links took the form:

..  code-block:: html

    <a href="javascript:linkTo_UnCryptMailto('nbjmup+jogpAfybnqmf/dpn')">info@example.com</a>

TYPO3 v12 and v13 removed the ``linkTo_UnCryptMailto`` JavaScript function in favour of
server-side ``data-cfemail`` encoding. Sites that were migrated from older TYPO3 versions
may still contain thousands of these obsolete JavaScript links stored directly in database
fields. Those links silently stop working in modern TYPO3 because the JavaScript function
no longer exists in the frontend.

This extension provides a single CLI command that:

*   Scans the database for records containing ``javascript:linkTo_UnCryptMailto(...)`` hrefs
*   Decrypts the Caesar-cipher-encoded email address
*   Replaces the obsolete ``<a>`` tag with a clean ``<a href="mailto:...">`` tag
*   Writes the corrected HTML back to the database

What it does **not** do
-----------------------

*   It does not add any frontend plugins, backend modules, or TypoScript configuration.
*   It does not change the visual appearance of your website.
*   It has no scheduler tasks and no recurring background jobs.
*   It does not require any configuration beyond the CLI options described in
    :ref:`commands`.

This is a **one-time migration utility** designed to be run once (or verified with
``--dry-run`` first) and then forgotten.

Features
========

*   Safely reverses legacy Caesar-cipher encrypted email links stored in the database.
*   Rebuilds broken ``<a href="javascript:...">`` tags into standard ``<a href="mailto:...">`` tags.
*   Dynamically scans the global TYPO3 TCA to find and process all Rich Text (RTE) and
    standard ``text`` fields across every registered table.
*   Includes a ``--dry-run`` mode that previews all changes without writing to the database.
*   Supports targeting a single record by UID, a specific table/field combination, or all
    RTE fields site-wide.
*   Validates decrypted addresses with TYPO3's built-in email validator before writing.
*   Handles Unicode-escaped characters (e.g. ``@`` for ``@``) in encoded parameters.

TYPO3 Compatibility
===================

.. list-table::
   :header-rows: 1

   * - Mail Link Fixer version
     - TYPO3 version
     - PHP version
   * - 1.x
     - 13.4 LTS
     - 8.2+
   * - 1.x
     - 14.3 LTS
     - 8.2+