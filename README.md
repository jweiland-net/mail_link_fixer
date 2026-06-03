# TYPO3 Extension `mail_link_fixer`

[![Packagist][packagist-logo-stable]][extension-packagist-url]
[![Latest Stable Version][extension-build-shield]][extension-ter-url]
[![Total Downloads][extension-downloads-badge]][extension-packagist-url]
[![Monthly Downloads][extension-monthly-downloads]][extension-packagist-url]
[![TYPO3 13.4][TYPO3-13-shield]][TYPO3-13-url]
[![TYPO3 14.3][TYPO3-14-shield]][TYPO3-14-url]

![Build Status](https://github.com/jweiland-net/mail_link_fixer/actions/workflows/ci.yml/badge.svg)

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

```
composer require jweiland/mail-link-fixer
```

### 2.2 Command Usage

The extension registers the following CLI command via the TYPO3 Console:

```bash
vendor/bin/typo3 mail_link_fixer:fix-spam-email [options]
```

#### Options

| Option | Short | Description |
|--------|-------|-------------|
| `--dry-run` | | Preview changes without writing to the database. |
| `--uid=<UID>` | | Process only the `tt_content` record with this UID. |
| `--table=<table>` | `-t` | Specific table to process (default: `tt_content`). |
| `--field=<field>` | `-f` | Specific field to process (default: `bodytext`). |
| `--all-rte` | | Scan all tables and fields in TCA configured with `enableRichtext=true`. |

#### Examples

Preview all changes without touching the database:

```bash
vendor/bin/typo3 mail_link_fixer:fix-spam-email --dry-run
```

Fix a single record by UID:

```bash
vendor/bin/typo3 mail_link_fixer:fix-spam-email --uid=42
```

Process a specific table and field:

```bash
vendor/bin/typo3 mail_link_fixer:fix-spam-email --table=tx_myext_domain_model_news --field=bodytext
```

Scan and fix all RTE fields across the entire TCA:

```bash
vendor/bin/typo3 mail_link_fixer:fix-spam-email --all-rte
```

Combine `--all-rte` with `--dry-run` for a safe full-site preview:

```bash
vendor/bin/typo3 mail_link_fixer:fix-spam-email --all-rte --dry-run
```

Add `-v` for verbose output that shows the number of replacements per record:

```bash
vendor/bin/typo3 mail_link_fixer:fix-spam-email --all-rte -v
```

<!-- MARKDOWN LINKS & IMAGES -->

[extension-build-shield]: https://poser.pugx.org/jweiland/mail-link-fixer/v/stable.svg?style=for-the-badge

[extension-downloads-badge]: https://poser.pugx.org/jweiland/mail-link-fixer/d/total.svg?style=for-the-badge

[extension-monthly-downloads]: https://poser.pugx.org/jweiland/mail-link-fixer/d/monthly?style=for-the-badge

[extension-ter-url]: https://extensions.typo3.org/extension/mail_link_fixer/

[extension-packagist-url]: https://packagist.org/packages/jweiland/mail-link-fixer/

[packagist-logo-stable]: https://img.shields.io/badge/--grey.svg?style=for-the-badge&logo=packagist&logoColor=white

[TYPO3-13-url]: https://get.typo3.org/version/13

[TYPO3-13-shield]: https://img.shields.io/badge/TYPO3-13.4-green.svg?style=for-the-badge&logo=typo3

[TYPO3-14-url]: https://get.typo3.org/version/14

[TYPO3-14-shield]: https://img.shields.io/badge/TYPO3-14.3-green.svg?style=for-the-badge&logo=typo3
