# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Scope

This is the `ok_typo3_helper` TYPO3 extension (`oliverkroener/ok-typo3-helper`, extension key `ok_typo3_helper`) — reusable helper traits, services, and a Microsoft Graph mail converter that other Oliver Kröner extensions build on. It lives as a path-repository package inside the parent TYPO3 14 project; see `../../CLAUDE.md` for the DDEV environment, project-wide config layering, and the Fluid template workaround this extension hosts.

## Multi-version compatibility (important)

Unlike the parent project (TYPO3 14 only), this extension must run on **TYPO3 12.4 / 13.4 / 14** and **PHP 8.1+** (`composer.json` `typo3/cms-core: ^12 || ^13 || ^14`; `ext_emconf.php` constraint `12.4.0-14.4.99`). Do not introduce APIs, syntax, or DI patterns that only exist in a single major version — every change has to hold across all three. When bumping the version, update **both** `composer.json` (`version`) and `ext_emconf.php` (`version`) so they stay in sync.

## Architecture

Three independent, single-responsibility classes under `Classes/` (PSR-4 root `OliverKroener\Helpers\` → `Classes/`):

- **`MSGraphApi\MSGraphMailApiService`** — the core piece. `convertToGraphMessage(SentMessage): array` is **static** (no DI) and returns `['message' => Microsoft\Graph\...\Message, 'from' => ?string]`. It re-parses the already-sent MIME (`$rawMessage->toString()`) with `zbateson/mail-mime-parser`, then rebuilds From/To/Cc/Bcc/Reply-To, HTML-or-plain body, and attachments as Graph model objects. This is the conversion layer consumed by `ok_exchange365_mailer`'s transport — treat its return shape as a contract with that extension.
  - **Inline image (`cid:`) handling is the subtle part** and the subject of most recent commits. An attachment with `Content-Disposition: inline` gets `setIsInline(true)` **and** `setContentId($attachment->getContentId())`. Graph will *not* auto-generate a Content-ID matching the `cid:` reference in the HTML body, so it must be carried over verbatim or the `<img src="cid:…">` renders broken. `getContentId()` already returns the value stripped of surrounding `<>`, which is why it matches the body reference directly — don't re-wrap or re-strip it.
- **`Service\SiteRootService`** — singleton (`SingletonInterface`, registered `public: true` in `Configuration/Services.yaml`) resolving a page UID to its site-root page UID via `SiteFinder`; catches `SiteNotFoundException` and returns `null`. Uses `injectSiteFinder()` method injection (works across all supported versions). Inject it via DI; do not `GeneralUtility::makeInstance` it ad hoc.
- **`Traits\ReflectionPropertiesTrait`** — adds dynamic `getXxx()`/`setXxx()` to any class via `__call()` + reflection, reaching protected/private properties. Throws on unknown method or missing property.

`Configuration/Services.yaml` autoloads all of `Classes/*` (autowire, non-public by default); `SiteRootService` is the one explicit public + `singleton`-tagged override. `ext_localconf.php` is intentionally near-empty (`defined('TYPO3') || die();`).

## Commands

Code style, tests, and TYPO3 CLI are run from the **parent project root** via DDEV — this package has no standalone build. From `../../`:

```bash
ddev exec vendor/bin/php-cs-fixer fix                     # applies TYPO3 CGL to packages/* (this ext included)
ddev exec vendor/bin/php-cs-fixer fix --dry-run --diff    # check only
```

Documentation is the only thing built locally, from this directory (`Makefile`, renders `Documentation/` via the TYPO3 render-guides Docker image into the gitignored `Documentation-GENERATED-temp/`):

```bash
make docs        # render once (pulls latest render-guides image)
make docs-fast   # render without pulling
make docs-watch  # watch Documentation/ and re-render on change (needs inotify-tools: make watch-install)
```
