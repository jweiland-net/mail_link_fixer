..  include:: /Includes.rst.txt


..  _installation:

============
Installation
============

Composer
========

If your TYPO3 installation works in composer mode, execute the following command:

..  code-block:: bash

    composer require jweiland/mail-link-fixer

If you work with DDEV, use:

..  code-block:: bash

    ddev composer require jweiland/mail-link-fixer

The extension registers its CLI command automatically via Symfony Console and TYPO3's
autoconfigure mechanism. No additional setup step is required after installation.

ExtensionManager
================

On non-composer-based TYPO3 installations you can install ``mail_link_fixer`` via the
ExtensionManager:

..  rst-class:: bignums

1.  Login

    Login to the backend of your TYPO3 installation as an administrator or system maintainer.

2.  Open ExtensionManager

    Click on :guilabel:`Extensions` from the left menu to open the ExtensionManager.

3.  Update Extensions

    Choose :guilabel:`Get Extensions` from the upper selectbox and click on the
    :guilabel:`Update now` button at the upper right.

4.  Install ``mail_link_fixer``

    Use the search field to find ``mail_link_fixer``. Choose the result line and click the
    cloud icon to install the extension.

Verify the installation
=======================

To confirm the CLI command was registered successfully, run:

..  code-block:: bash

    vendor/bin/typo3 list | grep mail_link_fixer

You should see:

..  code-block:: text

    mail_link_fixer:fix-spam-email  A command that fixes spam protected email links in RTE fields.

Next step
=========

Read :ref:`commands` to learn how to run the migration.