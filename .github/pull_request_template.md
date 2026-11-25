<!-- Title: a Conventional Commit, for example "fix(stock): move transfer stock only once". See CONTRIBUTING.md. -->

## What

<!-- The change in a few sentences. Link the issue it closes: "Closes #123". -->

## Why

<!-- The problem it solves, or the behaviour that was wrong. -->

## How it was tested

<!-- The tests you added or changed, and what you checked by hand (page, account, demo data). Screenshots for visible changes. -->

## Checklist

- [ ] `composer lint`: Pint finds nothing to fix
- [ ] `composer analyse`: Larastan level 8 passes, with no baseline, `ignoreErrors` or `@phpstan-ignore` added
- [ ] `composer refactor`: Rector has nothing left to change
- [ ] `composer test` passes against a disposable test database (`make test` in Docker)
- [ ] Views, scripts or styles changed: `npm test` and `npm run build` pass, and `node tests/Feature/Frontend/check-manifest.mjs` finds every file
- [ ] A fixed bug has a regression test that fails without the fix
- [ ] A new route carries a seeded permission; a new GET page under `/dashboard` is in the sidebar's `active` patterns
- [ ] Schema changes come as a new migration with a working `down()`; no merged migration was edited
- [ ] `CHANGELOG.md` has a line under `[Unreleased]` for a change users, operators or `/api` clients notice; a breaking change is marked with `!`
- [ ] No real patient data, personal e-mail addresses or phone numbers in code, fixtures or screenshots
