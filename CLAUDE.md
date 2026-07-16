# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`ok_typo3_helper` (`oliverkroener/ok-typo3-helper`) is a TYPO3 10.4 LTS extension providing reusable helper traits and utilities. It is a **library extension** — it registers no plugins, backend modules, TCA, or routes. `ext_localconf.php` is intentionally empty apart from the `TYPO3_MODE` guard; all wiring is PSR-4 autoload + Symfony DI (`Configuration/Services.yaml` autowires everything under `Classes/`).

Target: PHP 7.2+ and TYPO3 `^10.4` only. Do not introduce syntax or APIs newer than PHP 7.2 or TYPO3 v10.

## Commands

```bash
composer run stan       # PHPStan level 5 (via saschaegerer/phpstan-typo3 stubs), analyses Classes/
composer run cs:check   # PHP-CS-Fixer dry-run, TYPO3 coding-standards preset
composer run cs:fix     # PHP-CS-Fixer apply
composer run stan:baseline   # regenerate phpstan-baseline.neon (then uncomment its include in phpstan.neon)

make docs               # render RST docs (Documentation/) to Documentation-GENERATED-temp/ via Docker
make docs-watch         # re-render on file changes (needs inotify-tools)
```

There is **no test suite** — quality gates are PHPStan + PHP-CS-Fixer. Verify changes with `composer run stan` and `composer run cs:check`.

## Layout notes

- `Classes/` (PSR-4 root `OliverKroener\Helpers\`) is the only shipped source. Two components live here:
  - `MSGraphApi/MSGraphMailApiService.php` — the core of the extension.
  - `Traits/ReflectionPropertiesTrait.php` — magic `getX()`/`setX()` accessors backed by reflection.
- `public/` is a git-ignored TYPO3 core checkout for local dev only — **not part of the extension**. Ignore it when reasoning about the package's own code.
- `Documentation-GENERATED-temp/` is build output (git-ignored). Edit `Documentation/*.rst`, never the generated HTML.
- Version lives in **three** places that must stay in sync: `composer.json` (`version`), `ext_emconf.php` (`version`), and the README badge.

## MSGraphMailApiService

`MSGraphMailApiService::convertToGraphMessage(SentMessage): array` is a pure, static converter with no TYPO3/DI dependencies. It parses a Symfony `SentMessage` (raw MIME, via `zbateson/mail-mime-parser`) and returns `['message' => Microsoft\Graph\Model\Message, 'from' => string]`. The returned Graph message is meant to be handed to the Graph `sendMail` endpoint; this class does **not** send mail itself.

Behavior that is easy to break — preserve it:
- Recipient headers (From/To/CC/BCC/Reply-To) are only read when the parsed header is an `AddressHeader`; guard every header access with `instanceof` like the existing code.
- Body prefers HTML, falls back to plain text, then to empty content.
- Attachments are skipped unless the part is an `IMimePart`.
- **Inline images (`cid:`)**: parts with `Content-Disposition: inline` get `setIsInline(true)` and — critically — `setContentId($attachment->getContentId())`. Graph does not regenerate the Content-ID, so dropping this call renders inline images broken in delivered mail.

`phpstan.neon` carries one deliberate `ignoreErrors` entry for a `setContentBytes()` typing quirk in `microsoft/microsoft-graph` v1 (StreamInterface vs concrete Stream). It is a third-party typing issue, not a bug to "fix" in this code.
