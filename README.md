# TYPO3 Extension `resolve_unsecure_mail`

[![Packagist][packagist-logo-stable]][extension-packagist-url]
[![Latest Stable Version][extension-build-shield]][extension-ter-url]
[![Total Downloads][extension-downloads-badge]][extension-packagist-url]
[![Monthly Downloads][extension-monthly-downloads]][extension-packagist-url]
[![TYPO3 13.4][TYPO3-shield]][TYPO3-13-url]

![Build Status][exttunnension-ci-shield]

This extension provides a CLI command to migrate legacy `javascript:linkTo_UnCryptMailto()` links in the database into standard `mailto:` links, allowing modern TYPO3 versions (v12/v13) to natively handle frontend spam protection.

## 1 Features

* Safely reverses legacy Caesar-cipher encrypted email links directly in the database.
* Rebuilds broken `<a href="javascript:...">` tags into standard `<a href="mailto:...">` tags.
* Dynamically scans the global TYPO3 TCA to find and process all Rich Text (RTE) and standard text fields.
* Includes a robust `--dry-run` mode to preview changes without altering database records.

## 2 Usage

### 2.1 Installation

#### Installation using Composer

The recommended way to install the extension is using Composer.

Run the following command within your Composer based TYPO3 project:

```bash
composer require jweiland/resolve-unsecure-mail
