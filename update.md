# Upgrade: wp_mailworkflow -> TYPO3 14 compatibility (v13.4 preserved)

## Target versions

- **Target TYPO3**: `^13.4 || ^14.0` (dual-version support, not a hard v14-only cut)
- **Target PHP**: `^8.2` (minimum shared by TYPO3 13.4 and 14.0; 8.3 recommended for local testing)
- **typo3/testing-framework**: `^9.0` (per official version matrix: 9.x.x supports TYPO3 v13 + v14, PHP 8.2-8.5)

## Context

This fork prepares `webprofil/wp-mailworkflow` for a PR to `Gernott/wp_mailworkflow`,
adding TYPO3 14 compatibility while keeping TYPO3 13.4 working. No secrets, no
unrelated refactors, no view-path fixes were touched (see "Known pre-existing
issues" below for what was found but intentionally left alone).

## Task checklist

- [x] Update `composer.json` core/testing-framework constraints for dual v13/v14 support
- [x] Replace `MailMessage::send()` (removed in core, no `send()` method left on
      `TYPO3\CMS\Core\Mail\MailMessage` as of TYPO3 14) with `MailerInterface::send()`
- [x] Migrate Extbase docblock annotations (`@Validate`, `@Cascade`, `@IgnoreValidation`)
      to PHP attributes (removed/unsupported as docblock annotations in TYPO3 14,
      see Breaking-107229)
- [x] `php -l` on all changed PHP files (via `php:8.3-cli-alpine` container, no local PHP available)
- [x] `composer validate --no-check-publish`
- [x] `composer update --dry-run --no-install` dependency resolution check (v13 and v14 candidates)
- [ ] No automated test suite exists in this extension (`Tests/` autoload namespace declared
      in composer.json, but no `Tests/` directory present) — could not run/verify functional
      or unit tests as part of this pass
- [ ] Manual smoke test in a running TYPO3 13.4 and TYPO3 14 instance (backend module load,
      recipient CRUD, queue list, scheduled mail send) — not possible in this sandbox
      (no DDEV/TYPO3 bootstrap available, only Docker + PHP CLI)
- [ ] Fix stale `Backend/` sub-path references in `SendQueueCommand` argument descriptions
      (see "Known pre-existing issues") — intentionally out of scope for this PR

## Files changed (7)

| File | Change |
|---|---|
| `composer.json` | `typo3/cms-core` -> `^13.4 \|\| ^14.0`; `typo3/testing-framework` -> `^9.0` (was `^13.4`, an invalid/unresolvable constraint — testing-framework has its own versioning scheme, never released a `13.x`) |
| `Classes/Command/SendQueueCommand.php` | Added `Symfony\Component\Mailer\MailerInterface` import + `$mailer` property + `injectMailer()` setter (same DI convention as existing `injectQueueRepository()`); replaced `$email->send()` with `$this->mailer->send($email)` |
| `Classes/Controller/RecipientController.php` | `@IgnoreValidation("recipient")` docblock -> `#[IgnoreValidation(['argumentName' => 'recipient'])]` attribute on `editAction()` |
| `Classes/Domain/Model/Mail.php` | 4x `@Validate("NotEmpty")` -> `#[Validate(['validator' => 'NotEmpty'])]` (title, daysToSend, subject, mailtext); 1x `@Cascade("remove")` -> `#[Cascade(['value' => 'remove'])]` (attachment) |
| `Classes/Domain/Model/MailGroup.php` | 1x `@Validate` -> attribute (title); 1x `@Cascade` -> attribute (mails) |
| `Classes/Domain/Model/Queue.php` | 1x `@Validate` -> attribute (sendAt) |
| `Classes/Domain/Model/Recipient.php` | 2x `@Validate` -> attribute (start, email) |

No other files needed changes. `Classes/Domain/Repository/*` and `Classes/Controller/QueueController.php`
use no removed APIs and needed no edits.

## Why the old `Annotation` namespace was kept (not `Attribute`)

TYPO3 14 breaking change
[Breaking-107229-RemovedSupportOfAnnotationsInExtbase](https://docs.typo3.org/permalink/changelog:breaking-107229)
removed **docblock annotation parsing** and introduces a new
`TYPO3\CMS\Extbase\Attribute` namespace as the "correct" home for these classes going
forward. However, `TYPO3\CMS\Extbase\Attribute\{Validate,Cascade,IgnoreValidation}`
classes only exist starting with TYPO3 14 (confirmed: present on `main`, absent on the
`13.4` branch of `TYPO3/typo3`). Since this extension must keep working on TYPO3 13.4 too,
the existing `TYPO3\CMS\Extbase\Annotation\*` classes were kept and used as **PHP
attributes** (`#[Validate(...)]` instead of `@Validate(...)`) instead of switching
namespaces. These classes have carried native `#[\Attribute]` support since TYPO3 v12,
so this works unchanged on both 13.4 and 14.x. TYPO3 14 keeps a backward-compatible class
alias for the old namespace (deprecated but functional) — a namespace migration to
`Extbase\Attribute` can be done later in a v14-only follow-up once v13 support is dropped.

Constructor payload shapes were taken directly from the TYPO3 13.4 core source
(`typo3/sysext/extbase/Classes/Annotation/{Validate,IgnoreValidation}.php` and
`Annotation/ORM/Cascade.php`):

- `Validate(['validator' => string, 'options' => array, 'param' => string])`
- `Cascade(['value' => string])` (only `'remove'` supported by Extbase)
- `IgnoreValidation(['argumentName' => string])`

## MailMessage -> MailerInterface

`TYPO3\CMS\Core\Mail\MailMessage` (extends Symfony `Email`) no longer has a `send()`
method in the current TYPO3 core source — sending must go through
`Symfony\Component\Mailer\MailerInterface::send(RawMessage $message)`, implemented by
TYPO3's `TYPO3\CMS\Core\Mail\Mailer` service. `SendQueueCommand` now injects
`MailerInterface` via the same `inject*()` setter-DI convention the class already used for
`QueueRepository` (works because `Configuration/Services.yaml` autowires/autoconfigures all
classes under `WEBprofil\WpMailworkflow\`), and calls `$this->mailer->send($email)` instead
of `$email->send()`. This is compatible with both TYPO3 13.4 and 14.x — `MailerInterface`
injection has been the recommended API since TYPO3 v10.

## Checks run and results

| Check | Command | Result |
|---|---|---|
| PHP lint (all 7 changed files + 3 untouched repositories for regression sanity) | `docker run --rm -v ... php:8.3-cli-alpine php -l <file>` | **Pass** — "No syntax errors detected" for all files |
| Composer schema validation | `composer validate --no-check-publish` (run inside `php:8.3-cli-alpine` container, no local PHP/Composer installed on host) | **Pass** — "./composer.json is valid" |
| Dependency resolution (no lockfile written) | `composer update --dry-run --no-install --ignore-platform-req=ext-intl` | **Pass** — resolves to `typo3/cms-core` satisfiable by `v13.4.31/32/33` and `v14.3.3/4/5`; full dry-run lock simulation completed successfully picking `typo3/cms-core v14.3.5` + `typo3/testing-framework 9.6.1`, confirming the v14 path resolves end-to-end |

Notes on the environment:
- No PHP, Composer, or DDEV project bootstrap was available on the host for this sandboxed
  fork; `docker` was available and used to run `php:8.3-cli-alpine` with a fresh Composer
  install (`getcomposer.org/installer`) for all checks above. `ext-intl` is not compiled
  into that base image, so dependency resolution needed `--ignore-platform-req=ext-intl`;
  a real deployment target has `ext-intl` per TYPO3's requirements, so this is a sandbox
  artifact only, not a real blocker.
- No `.lock` file was written or committed; only `--dry-run` / `--no-install` operations
  were used, per instructions.
- Git: only working-tree edits were made in this fork clone; no commits, no pushes.

## Known pre-existing issues found but NOT fixed (out of scope, documented only)

- **Stale `Backend/` template path references**: commit `7460ea0` ("[BUGFIX] Template
  fixes for TYPO3 13") moved `Resources/Private/Backend/{Templates,Partials,Layouts}/*`
  up one level to `Resources/Private/{Templates,Partials,Layouts}/*`, but
  `Classes/Command/SendQueueCommand.php` argument descriptions (used as scheduler-task
  help text for `templatesPath`, `partialsPath`, `layoutsPath`) still say
  `EXT:wp_mailworkflow/Resources/Private/Backend/Templates/` etc. Any operator following
  that help text literally when configuring a scheduler task would point at a directory
  that no longer exists. This is a pure documentation/help-text mismatch (the arguments
  are user-supplied at runtime, not hardcoded defaults), left untouched per scope
  ("keine unrelated View-Pfad-Fixes").
- **No `Tests/` directory**: `composer.json` declares a `WEBprofil\WpMailworkflow\Tests\`
  autoload-dev namespace pointing at `Tests/`, but that directory does not exist in the
  repository. There is no automated test coverage to run/extend as part of this upgrade.

## Open risks / follow-ups for the PR description

1. No automated tests exist — the changes above were only verified via `php -l`,
   `composer validate`, and a dependency-resolution dry run, not via functional/unit
   tests or a running TYPO3 instance.
2. View/template paths should be spot-checked manually in a real TYPO3 13.4 and TYPO3 14
   instance (backend module `wp_mailworkflow`, actions `Recipient::list/new/edit`,
   `Queue::list`) — no such instance was available in this sandbox.
3. `SendQueueCommand` help text references a stale `Backend/` sub-path (see above);
   worth a small separate follow-up cleanup PR.
4. `Extbase\Annotation\*` namespace is deprecated as of TYPO3 14 (class-alias-map
   backward compatibility only) — once TYPO3 13 support is eventually dropped, switch to
   `Extbase\Attribute\*`.
5. `ext-intl` must be present in the actual deployment/CI runtime (already implied by
   TYPO3 core's own requirements); it was just missing from the throwaway Docker image
   used for sandbox verification.

## Reviewer follow-up: separate TYPO3-13-only dry-run

The checks above already ran a combined dry-run against `^13.4 || ^14.0`, which Composer
resolved by picking the v14 branch (`typo3/cms-core v14.3.5`). A reviewer asked for an
**explicit, isolated** dry-run of the TYPO3 13 branch, to make sure `^13.4` alone still
resolves cleanly with `typo3/testing-framework ^9.0`, without touching this fork's real
`composer.json` or `composer.lock`.

### Method

No lockfile exists in this fork, and `composer.json` was never touched for this check.
Instead of running `composer require --dry-run` (which does not support dry-run for
constraint changes) directly against this checkout, a **throwaway copy** of only
`composer.json` was made outside the fork, in `/tmp/opencode/v13_check/composer.json`,
with `typo3/cms-core` pinned to `^13.4` only (no `|| ^14.0`) while keeping
`typo3/testing-framework: "^9.0"` unchanged. This copy was resolved in a fresh, isolated
`php:8.3-cli-alpine` Docker container (separate container run, separate filesystem mount,
no shared state with the checks above).

```
# outside the fork, no mkdir/cat used (sandbox tooling quirk), via python3:
python3 -c "import os, shutil; os.makedirs('/tmp/opencode/v13_check', exist_ok=True); \
  shutil.copy('/tmp/opencode/wp_mailworkflow/composer.json', '/tmp/opencode/v13_check/composer.json')"
# then require pinned to "typo3/cms-core": "^13.4" only in that copy (testing-framework left at ^9.0)

docker run --rm -v /tmp/opencode/v13_check:/app -w /app php:8.3-cli-alpine sh -c '
  apk add --no-cache git unzip curl
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
  git config --global --add safe.directory /app
  composer validate --no-check-publish
  composer update --dry-run --no-install --ignore-platform-req=ext-intl
'
```

### Result: PASS

- `composer validate --no-check-publish` -> `./composer.json is valid`
- `composer update --dry-run --no-install --ignore-platform-req=ext-intl` -> full resolution
  succeeded, **110 installs, 0 updates, 0 removals**, no conflicts, no security advisories.
  Key resolved versions with `typo3/cms-core` pinned to `^13.4` only:
  - `typo3/cms-core v13.4.33`
  - `typo3/cms-backend v13.4.33`, `typo3/cms-extbase v13.4.33`, `typo3/cms-fluid v13.4.33`,
    `typo3/cms-frontend v13.4.33`
  - `typo3/testing-framework 9.6.1` (confirms `^9.0` resolves for the v13 branch too, not
    only for v14)
  - `doctrine/annotations 2.0.2` is pulled in on the v13 branch (still required there;
    only removed as an Extbase dependency in TYPO3 14, matching Breaking-107229) — Composer
    prints an "abandoned package" notice for it, which is expected/pre-existing on v13 and
    unrelated to this fork's changes.
  - `typo3/class-alias-loader v1.2.2` and `typo3fluid/fluid v4.6.1` differ from the
    v14 resolution (`v2.0.1`/`v5.3.1` respectively) — expected, those are TYPO3-version-
    pinned transitive dependencies, not something this extension controls.

This confirms both halves of the `^13.4 || ^14.0` constraint in this fork's real
`composer.json` resolve independently and cleanly: v13 in isolation (this check) and v14
in isolation (already shown by the combined dry-run picking the v14 branch in the checks
table above). No changes were made to this fork's `composer.json` or any lockfile for this
verification; the throwaway copy lived entirely under `/tmp/opencode/v13_check/` and was
discarded afterwards.
