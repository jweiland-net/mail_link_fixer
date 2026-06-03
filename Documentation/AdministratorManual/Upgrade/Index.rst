..  include:: /Includes.rst.txt


..  _upgrade:

=======
Upgrade
=======

If you upgrade ``EXT:mail_link_fixer`` to a newer version, please read this section.

Version 1.0.0
=============

This is the initial release. No upgrade steps are required.

The extension supports TYPO3 **13.4 LTS** and **14.3 LTS**. It does not support
TYPO3 12 or earlier versions. PHP 8.2 or higher is required.

General upgrade advice
======================

After updating any TYPO3 extension it is good practice to:

1.  Run :bash:`vendor/bin/typo3 cache:flush` to clear all TYPO3 caches.
2.  Re-run ``composer install`` if Composer is used, to update the autoloader.
3.  Re-check that the CLI command is still registered:

    ..  code-block:: bash

        vendor/bin/typo3 list | grep mail_link_fixer