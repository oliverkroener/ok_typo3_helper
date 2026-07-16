# TYPO3 Helpers (`ok_typo3_helper`)

[![TYPO3 12](https://img.shields.io/badge/TYPO3-12-orange?logo=typo3)](https://get.typo3.org/version/12)
[![TYPO3 13](https://img.shields.io/badge/TYPO3-13-orange?logo=typo3)](https://get.typo3.org/version/13)
[![TYPO3 14](https://img.shields.io/badge/TYPO3-14-orange?logo=typo3)](https://get.typo3.org/version/14)
[![PHP 8.1+](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/version-3.1.0-green)](https://github.com/oliverkroener/ok_typo3_helper)

Reusable helper traits, services, and a Microsoft Graph mail converter that other Oliver Kröner TYPO3 extensions build on.

---

## Features

- **Microsoft Graph mail converter** (`MSGraphMailApiService`) — converts a Symfony `SentMessage` into a Microsoft Graph `Message` object ready for the Graph `sendMail` endpoint. It maps From/To/Cc/Bcc/Reply-To recipients, HTML and plain-text bodies, and file attachments. This is the conversion layer used by [`ok_exchange365_mailer`](https://github.com/oliverkroener/ok_exchange365_mailer).
  - **Inline images (`cid:`) support** — attachments marked `Content-Disposition: inline` are flagged as inline **and** their original MIME `Content-ID` is carried onto the Graph `FileAttachment`. Without this, Graph cannot bind an `<img src="cid:…">` in the HTML body to its attachment and the image renders broken. The Content-ID is read via `getContentId()`, which already returns the value without the surrounding `<>` so it matches the `cid:` reference in the body verbatim.
- **Site root resolver** (`SiteRootService`) — a singleton service that returns the site root page UID for any given page UID, resolving `SiteNotFoundException` to `null`.
- **Reflection accessor trait** (`ReflectionPropertiesTrait`) — adds dynamic `getXxx()` / `setXxx()` access to any class's properties (including protected/private) via `__call()` and reflection.

---

## Requirements

- TYPO3 12.4 LTS, 13.4 LTS, or 14 LTS
- PHP 8.1 or newer
- [`microsoft/microsoft-graph`](https://packagist.org/packages/microsoft/microsoft-graph) `^2` (pulled in automatically)
- [`zbateson/mail-mime-parser`](https://packagist.org/packages/zbateson/mail-mime-parser) `^3` (pulled in automatically)

---

## Installation

Install via Composer:

```bash
composer require oliverkroener/ok-typo3-helper
```

As a local path-repository extension (as used in this project), add the package under `packages/` and require it as a dev version:

```bash
composer require oliverkroener/ok-typo3-helper:@dev
```

Then set it up:

```bash
vendor/bin/typo3 extension:setup
vendor/bin/typo3 cache:flush
```

---

## Usage

### Converting a message for Microsoft Graph

`MSGraphMailApiService::convertToGraphMessage()` is `static` and takes a Symfony `SentMessage`, returning an array with the built Graph `message` and the resolved `from` address:

```php
use OliverKroener\Helpers\MSGraphApi\MSGraphMailApiService;

$result = MSGraphMailApiService::convertToGraphMessage($sentMessage);
$graphMessage = $result['message']; // Microsoft\Graph\Generated\Models\Message
$fromAddress  = $result['from'];    // string|null
```

Inline images work automatically: build the mail with Symfony's `->embed()` (or a TYPO3 Fluid mail template with an inline image), and the resulting `cid:` reference in the HTML body is preserved end-to-end into the Graph attachment's `contentId`.

### Resolving the site root

```php
use OliverKroener\Helpers\Service\SiteRootService;

// Injected via DI (registered public in Configuration/Services.yaml)
$rootPageId = $siteRootService->findNextSiteRoot($currentPageId); // int|null
```

### Dynamic property accessors

```php
use OliverKroener\Helpers\Traits\ReflectionPropertiesTrait;

class MyModel
{
    use ReflectionPropertiesTrait;

    private string $title = '';
}

$model = new MyModel();
$model->setTitle('Hello');   // routed through __call → reflection
echo $model->getTitle();     // "Hello"
```

---

## Architecture

| Component | Class | Role |
| --- | --- | --- |
| Graph mail converter | `OliverKroener\Helpers\MSGraphApi\MSGraphMailApiService` | Parses a MIME `SentMessage` and rebuilds it as a Graph `Message` |
| Site root resolver | `OliverKroener\Helpers\Service\SiteRootService` | Maps a page UID to its site root page UID (singleton, DI-registered) |
| Reflection trait | `OliverKroener\Helpers\Traits\ReflectionPropertiesTrait` | Dynamic getters/setters via `__call()` + reflection |

```
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
├── ext_emconf.php
├── ext_localconf.php
└── composer.json
```

---

## License

This extension is licensed under the [GPL-2.0-or-later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html). See the [LICENSE](LICENSE) file for the full text.

---

## Author — Oliver Kroener

### Automated. Scaled. Done.

Web3 · Cloud · Automation

Technology is only valuable when it solves a real problem. For over 30 years I've been translating between business and tech — so your investment in digitalisation doesn't stall at proof-of-concept but delivers measurable results.

- Website: [oliver-kroener.de](https://www.oliver-kroener.de)
- Web3: [web3.oliver-kroener.de](https://web3.oliver-kroener.de/)
- Email: [ok@oliver-kroener.de](mailto:ok@oliver-kroener.de)
- Web3 Email: [oliverkroener@ethermail.io](mailto:oliverkroener@ethermail.io)
