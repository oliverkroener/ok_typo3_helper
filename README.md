# TYPO3 Helpers (`ok_typo3_helper`)

[![TYPO3 10](https://img.shields.io/badge/TYPO3-10-orange?logo=typo3)](https://get.typo3.org/version/10)
[![PHP 7.2+](https://img.shields.io/badge/PHP-7.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
[![Version](https://img.shields.io/badge/version-2.0.0-green)](https://github.com/oliverkroener/ok_typo3_helper)
[![Microsoft Graph](https://img.shields.io/badge/Microsoft%20Graph-API-0078D4?logo=microsoft)](https://learn.microsoft.com/en-us/graph/)

Reusable helper traits and utilities for TYPO3 — including a Microsoft Graph mail conversion service that turns a Symfony `SentMessage` into a Graph-ready message object.

## Features

- **Microsoft Graph mail conversion** — `MSGraphMailApiService::convertToGraphMessage()` parses a raw Symfony `SentMessage` (MIME) and builds a `Microsoft\Graph\Model\Message` ready to be sent through the Microsoft Graph `sendMail` endpoint.
  - Maps **From**, **To**, **CC**, **BCC** and **Reply-To** recipients (name + address).
  - Handles both **HTML** and **plain-text** bodies, with a sensible fallback.
  - Converts **file attachments** to Graph `FileAttachment` objects (base64-encoded content bytes).
  - Supports **inline images (cid:)** — inline parts are flagged with `isInline` and carry their original **Content-ID** so `cid:` references in the HTML body render correctly.
- **`ReflectionPropertiesTrait`** — a reusable trait that provides dynamic `getX()` / `setX()` magic accessors for any class's properties via reflection.

## Requirements

- **TYPO3:** 10.4 LTS
- **PHP:** 7.2 or higher
- **Dependencies:**
  - [`microsoft/microsoft-graph`](https://github.com/microsoftgraph/msgraph-sdk-php) `^1`
  - [`zbateson/mail-mime-parser`](https://github.com/zbateson/mail-mime-parser) `^2`

## Installation

Install via Composer:

```bash
composer require oliverkroener/ok-typo3-helper
```

For local development with a path repository, add the following to your project's root `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/*"
        }
    ]
}
```

Then require and activate the extension:

```bash
composer require oliverkroener/ok-typo3-helper:@dev
vendor/bin/typo3 extension:activate ok_typo3_helper
```

## Usage

### Convert a message for Microsoft Graph

```php
use OliverKroener\Helpers\MSGraphApi\MSGraphMailApiService;
use Symfony\Component\Mailer\SentMessage;

/** @var SentMessage $sentMessage */
$result = MSGraphMailApiService::convertToGraphMessage($sentMessage);

$graphMessage = $result['message']; // Microsoft\Graph\Model\Message
$fromAddress  = $result['from'];    // string, the sender address

// $graphMessage can now be passed to the Microsoft Graph "sendMail" request.
```

Inline images referenced in the HTML body via `cid:` are detected automatically from
their `Content-Disposition: inline` header and carried over with their Content-ID, so
embedded graphics render in the delivered mail.

### Dynamic property accessors

```php
use OliverKroener\Helpers\Traits\ReflectionPropertiesTrait;

class Contact
{
    use ReflectionPropertiesTrait;

    private string $email = '';
}

$contact = new Contact();
$contact->setEmail('ok@oliver-kroener.de'); // magic setter
echo $contact->getEmail();                   // magic getter
```

## Development

The extension ships with static analysis and coding-standards tooling:

```bash
composer run stan       # PHPStan (level 5, TYPO3 stubs)
composer run cs:check   # PHP-CS-Fixer dry-run (TYPO3 coding standards)
composer run cs:fix     # PHP-CS-Fixer apply
```

## License

This extension is licensed under [GPL-2.0-or-later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).

## Author — Oliver Kroener

### Automated. Scaled. Done.

Web3 · Cloud · Automation

Technology is only valuable when it solves a real problem. For over 30 years I've been translating between business and tech — so your investment in digitalisation doesn't stall at proof-of-concept but delivers measurable results.

- Website: [oliver-kroener.de](https://www.oliver-kroener.de)
- Web3: [web3.oliver-kroener.de](https://web3.oliver-kroener.de/)
- Email: [ok@oliver-kroener.de](mailto:ok@oliver-kroener.de)
- Web3 Email: [oliverkroener@ethermail.io](mailto:oliverkroener@ethermail.io)
