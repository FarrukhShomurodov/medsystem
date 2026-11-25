# Security policy

MedSystem stores patient records, prescriptions, stock and payments. A security report is handled
before any other work on the repository.

## Supported versions

| Version | Supported |
|---|---|
| `main` (2.x) | Yes |
| 1.x, the original application before the 2.0.0 refactor | No |

Fixes land on `main` only; there are no maintained release branches. Check a finding against the
latest `main` before you report it.

MedSystem is a portfolio project. It has not been certified or audited for clinical use. Whoever
runs it with real patient data is responsible for that deployment: HTTPS, backups, access to the
server and compliance with the data-protection law that applies to them.

## Reporting a vulnerability

Report it privately through GitHub: open the repository's **Security** tab and choose
**Report a vulnerability**
([direct link](https://github.com/FarrukhShomurodov/medsystem/security/advisories/new)).
Do not open a public issue, pull request or discussion for it, and do not use e-mail.

A useful report contains:

- the affected route, page, endpoint or class, and the commit you tested;
- the steps to reproduce on a local installation with the demo data
  (`APP_ENV=local php artisan migrate:fresh --seed`; the demo accounts are created by
  `database/seeders/DemoSeeder.php`);
- what an attacker gains, and which account or permission they need first.

Never put real patient data into a report. The demo data is fictional, and a local installation
is enough to show any of the issues in scope.

What happens next:

1. The report is acknowledged, normally within a week, and you are told whether it is accepted.
2. An accepted issue is fixed on `main` together with a regression test.
3. A GitHub security advisory is published after the fix, crediting you unless you ask not to be
   named.

Test only against an installation you run yourself. Do not test against a deployment that is not
yours.

## Scope

In scope is the code and configuration in this repository:

- **Authentication and sessions:** sign-in on the `admin` guard, its throttle, "remember me",
  sign-out, and the end of a dismissed employee's session (`EnsureAccountIsActive`).
- **Authorization:** every dashboard route and every AJAX route under `/api` requires the admin
  session and a permission (spatie/laravel-permission); record rules live in `app/Policies`. Reading
  or changing data without the permission the page or endpoint requires is in scope.
- **Patient data:** patient records, uploaded patient files (private disk, served by
  `PatientFileController`) and the AJAX endpoints in `routes/ajax.php`.
- **Injection:** SQL injection, cross-site scripting through Blade views or the page scripts in
  `resources/js`, and cross-site request forgery on any write.
- **Workflow integrity:** a way to pay an order twice, take stock out twice or below zero, book a
  doctor twice for the same time, or skip a step of a status lifecycle (registrar confirmation,
  cashier payment, prescriptions, transfers, supply requests).
- **Shipped configuration:** the middleware in `bootstrap/app.php` (`SecurityHeaders`,
  `RejectMalformedInput`), `docker/`, `compose.yaml` and the defaults in `.env.example`.

Out of scope:

- The demo accounts and their published password. `DemoSeeder` runs only when `APP_ENV=local`, and
  `AdminSeeder` refuses to create the administrator with the default password in production.
- Development tools: Laravel Telescope, which boots only when `APP_ENV=local`, and the Vite dev
  server.
- The missing `Content-Security-Policy` header. This is known: a few views still carry inline
  scripts. The other browser hardening headers are sent by `SecurityHeaders`.
- Vulnerabilities inside a dependency. Report those to the dependency's maintainers. A report is in
  scope when MedSystem uses a dependency in a way that makes the flaw exploitable. Dependabot
  proposes the updates; the CI dependency audit fails on any Composer advisory and on high-severity
  advisories in the npm packages that reach the browser. Advisories in build-only npm packages,
  which never reach `public/build`, are not in scope.
- Denial of service by request volume, findings from automated scanners without a working
  proof, social engineering, and attacks that need physical access to a signed-in workstation.
- Installations run by other people.
