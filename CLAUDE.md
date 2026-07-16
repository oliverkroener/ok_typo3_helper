# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this extension is

`ok_typo3_helper` (package `oliverkroener/ok-typo3-helper`, PSR-4 namespace
`OliverKroener\Helpers\`) is a **library extension**: a small set of reusable
helper services, traits and utilities meant to be *consumed by other TYPO3
extensions*. It has no plugins, backend modules, TCA, TypoScript or frontend
output of its own — the "API" is the public PHP classes under `Classes/`.

Because the classes are consumed externally, PHPStan/php-cs-fixer analyse them
in isolation and cannot see real usage — this is why `phpstan.neon` explicitly
ignores the "trait used zero times" notice for `ReflectionPropertiesTrait`.

## Branch / version context

This checkout is the **`feature-typo3-11`** branch, pinned to **TYPO3 11.5 /
PHP 7.4+**, version **2.0.1** (`composer.json` `require: typo3/cms-core: ^11.5`;
`ext_emconf.php` `depends.typo3: 11.5.0-11.5.99`). The `main` branch tracks
newer TYPO3 majors at a higher version (3.x). Keep this branch's constraints,
version numbers, and docs on TYPO3 11 — do not "upgrade" them to match `main`
unless explicitly asked.

## Development environment

This package is developed **inside a parent TYPO3 project** at
`/home/oliver/typo3-11/` (DDEV-based). All dev tooling (PHPStan,
php-cs-fixer, the TYPO3 coding-standards + `saschaegerer/phpstan-typo3`
packages) is installed in the **parent project's** `vendor/`, not here — this
package has no `require-dev` and no `vendor/` of its own. Run analysis commands
from the parent project root, pointing at this package's config files.

## Commands

Run from the **parent project root** (`/home/oliver/typo3-11`):

```bash
# Static analysis (level 5; phpstan-typo3 auto-registered via extension-installer)
ddev exec vendor/bin/phpstan analyse -c packages/ok_typo3_helper/phpstan.neon

# Coding standards (TYPO3 CGL via typo3/coding-standards preset)
vendor/bin/php-cs-fixer fix --config packages/ok_typo3_helper/.php-cs-fixer.dist.php
```

Run from **this package's** root for documentation (requires Docker):

```bash
make docs        # render Documentation/ -> Documentation-GENERATED-temp/ (pulls latest renderer image)
make docs-fast   # same, without re-pulling the image
make docs-watch  # rebuild on change (needs inotify-tools; `make watch-install` installs them)
```

There is **no automated test suite** in this package (no `Tests/` directory).

## Architecture: the three components

Each helper has a distinct integration contract — know which before using it:

- **`MSGraphApi\MSGraphMailApiService`** — a **stateless static converter**.
  `convertToGraphMessage(SentMessage)` parses a Symfony Mailer `SentMessage`
  (via `zbateson/mail-mime-parser`) and builds a Microsoft Graph
  `Message` model (`microsoft/microsoft-graph` v2), returning
  `['message' => Message, 'from' => string]`. It **does not authenticate or
  send** — the caller owns the Graph client and the `sendMail` call. It
  deliberately preserves inline attachments' `Content-ID` so `cid:` references
  in HTML bodies keep rendering (Graph won't regenerate them).

- **`Service\SiteRootService`** — a **DI singleton**, registered `public: true`
  in `Configuration/Services.yaml` (so it's fetchable via
  `GeneralUtility::makeInstance()` as well as injected). Wraps `SiteFinder` to
  return a page's root page UID, or `null` on `SiteNotFoundException`.

- **`Traits\ReflectionPropertiesTrait`** — a **mixin** adding magic
  `getXxx()`/`setXxx()` accessors that reach protected/private properties via
  reflection; throws `\Exception` for unknown methods/properties.

`Configuration/Services.yaml` autowires everything under `Classes/*`;
`SiteRootService` is the only entry given explicit `public: true` +
`singleton`. `ext_localconf.php` is intentionally a no-op guard
(`defined('TYPO3') || die();`) — there are no hooks to register.

## Documentation & metadata conventions

- Documentation lives in `Documentation/` (reST, TYPO3 render-guides). The
  `Documentation-GENERATED-temp/` output is git-ignored — never commit it.
- `README.md`, `Documentation/`, and metadata are kept in sync via the
  `typo3-toolkit:typo3-docs` skill. When changing version or TYPO3/PHP
  constraints, update `composer.json` **and** `ext_emconf.php` together (badges
  in README, `version`/`release` in `Documentation/guides.xml`, and
  `|release|` in `Documentation/Includes.rst.txt` must match).
- License is `GPL-2.0-or-later`.
