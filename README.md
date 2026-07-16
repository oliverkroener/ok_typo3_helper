# TYPO3 Helpers (`ok_typo3_helper`)

[![TYPO3 11](https://img.shields.io/badge/TYPO3-11-orange?logo=typo3)](https://get.typo3.org/version/11)
[![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/version-2.0.1-green)](https://github.com/oliverkroener/ok_typo3_helper)

A TYPO3 extension by Oliver Kroener providing reusable helper services, traits and utilities to streamline the development of other TYPO3 extensions.

---

## Features

- **Microsoft Graph mailer service** — `MSGraphMailApiService` converts a Symfony Mailer `SentMessage` into a Microsoft Graph-compatible `Message` object, ready to be sent through the Microsoft Graph API. It transfers:
  - `From`, `To`, `Cc`, `Bcc` and `Reply-To` recipients (address and display name)
  - HTML or plain-text body (auto-detected from the parsed MIME message)
  - Attachments, including inline images with their original `Content-ID`, so `cid:` references in the HTML body keep rendering
- **Site root service** — `SiteRootService` (a singleton) resolves the root page UID for a given page ID via TYPO3's `SiteFinder`, returning `null` when no site is configured for that page.
- **Reflection properties trait** — `ReflectionPropertiesTrait` adds magic `getXxx()` / `setXxx()` accessors that read and write protected/private properties through reflection.

---

## Requirements

- TYPO3 11.5 LTS
- PHP 7.4 or higher
- [`microsoft/microsoft-graph`](https://packagist.org/packages/microsoft/microsoft-graph) `^2` (installed automatically)
- [`zbateson/mail-mime-parser`](https://packagist.org/packages/zbateson/mail-mime-parser) `^2` (installed automatically)

---

## Installation

Install the extension via Composer:

```bash
composer require oliverkroener/ok-typo3-helper
```

To use a local checkout during development, add a path repository to your project's `composer.json`:

```json
{
    "repositories": [
        { "type": "path", "path": "packages/ok_typo3_helper" }
    ]
}
```

then require the package as above and activate it:

```bash
vendor/bin/typo3 extension:activate ok_typo3_helper
```

---

## Usage

### Sending mail through Microsoft Graph

Convert an outgoing Symfony `SentMessage` into a Microsoft Graph message and hand it to the Graph API:

```php
use OliverKroener\Helpers\MSGraphApi\MSGraphMailApiService;

$result = MSGraphMailApiService::convertToGraphMessage($sentMessage);
$graphMessage = $result['message']; // \Microsoft\Graph\Generated\Models\Message
$fromAddress  = $result['from'];    // string, the sender address
```

### Resolving the site root of a page

`SiteRootService` is registered as a public singleton and can be injected or fetched from the container:

```php
use OliverKroener\Helpers\Service\SiteRootService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$rootPageId = GeneralUtility::makeInstance(SiteRootService::class)
    ->findNextSiteRoot($currentPageId); // int|null
```

### Reflection-based accessors

Add magic getters and setters to any class:

```php
use OliverKroener\Helpers\Traits\ReflectionPropertiesTrait;

class Example
{
    use ReflectionPropertiesTrait;

    protected string $name = '';
}

$example = new Example();
$example->setName('TYPO3');   // writes the protected property
echo $example->getName();     // reads it back
```

---

## Architecture

| Component | Namespace | Responsibility |
| --------- | --------- | -------------- |
| `MSGraphMailApiService` | `OliverKroener\Helpers\MSGraphApi` | Parse a MIME `SentMessage` and build a Microsoft Graph `Message` |
| `SiteRootService` | `OliverKroener\Helpers\Service` | Resolve the site root page UID for a page ID |
| `ReflectionPropertiesTrait` | `OliverKroener\Helpers\Traits` | Magic reflection-based property accessors |

```text
ok_typo3_helper/
├── Classes/
│   ├── MSGraphApi/
│   │   └── MSGraphMailApiService.php
│   ├── Service/
│   │   └── SiteRootService.php
│   └── Traits/
│       └── ReflectionPropertiesTrait.php
├── Configuration/
│   └── Services.yaml
├── Resources/
│   └── Public/Icons/Extension.png
├── composer.json
├── ext_emconf.php
└── ext_localconf.php
```

---

## License

This extension is licensed under the [GPL-2.0-or-later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).

---

## Author — Oliver Kroener

### Automated. Scaled. Done.

Web3 · Cloud · Automation

Technology is only valuable when it solves a real problem. For over 30 years I've been translating between business and tech — so your investment in digitalisation doesn't stall at proof-of-concept but delivers measurable results.

- Website: [oliver-kroener.de](https://www.oliver-kroener.de)
- Web3: [web3.oliver-kroener.de](https://web3.oliver-kroener.de/)
- Email: [ok@oliver-kroener.de](mailto:ok@oliver-kroener.de)
- Web3 Email: [oliverkroener@ethermail.io](mailto:oliverkroener@ethermail.io)
